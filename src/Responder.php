<?php

namespace Flugg\Responder;

use Flugg\Responder\Contracts\Manager;
use Flugg\Responder\Contracts\Responder as ResponderContract;
use Flugg\Responder\Contracts\Transformable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection as FractalCollection;
use League\Fractal\Resource\Item as FractalItem;
use League\Fractal\Resource\NullResource as FractalNull;
use League\Fractal\Resource\ResourceInterface;

/**
 * The responder service. This class is responsible for generating JSON API responses.
 * It can also transform and serialize data using Fractal behind the scenes.
 *
 * @package Laravel Responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class Responder implements ResponderContract
{
    /**
     * Generate a successful JSON response.
     *
     * @param  mixed      $data
     * @param  int        $statusCode
     * @param  array|null $meta
     * @return JsonResponse
     */
    public function success( $data = null, $statusCode = 200, $meta = null ):JsonResponse
    {
        if ( is_integer( $data ) ) {
            list( $data, $statusCode, $meta ) = [ null, $data, $statusCode ];
        } elseif ( is_array( $statusCode ) ) {
            list( $statusCode, $meta ) = [ 200, $statusCode ];
        }

        $resource = $this->transform( $data );

        if ( is_array( $meta ) ) {
            $resource->setMeta( $meta );
        }

        $data = $this->serialize( $resource );
        $data = $this->includeStatusCode( $statusCode, $data );

        return response()->json( $data, $statusCode );
    }

    /**
     * Generate an unsuccessful JSON response.
     *
     * @param  string $errorCode
     * @param  int    $statusCode
     * @param  mixed  $message
     * @return JsonResponse
     */
    public function error( string $errorCode, int $statusCode = 500, $message = null ):JsonResponse
    {
        $response = $this->getErrorResponse( $errorCode, $statusCode );
        $messages = $this->getErrorMessages( $errorCode, $message );

        if ( count( $messages ) === 1 ) {
            $response[ 'error' ][ 'message' ] = $messages[ 0 ];
        } else if ( count( $messages ) > 1 ) {
            $response[ 'error' ][ 'messages' ] = $messages;
        }

        return response()->json( $response, $statusCode );
    }

    /**
     * Transforms the data.
     *
     * @param  mixed            $data
     * @param  Transformer|null $transformer
     * @return ResourceInterface
     */
    public function transform( $data = null, Transformer $transformer = null ):ResourceInterface
    {
        if ( is_null( $data ) ) {
            return new FractalNull();
        } elseif ( $data instanceof Transformable ) {
            return $this->transformModel( $data, $transformer );
        } elseif ( $data instanceof Collection ) {
            return $this->transformCollection( $data, $transformer );
        } elseif ( $data instanceof Builder ) {
            return $this->transformCollection( $data->get(), $transformer );
        } elseif ( $data instanceof LengthAwarePaginator ) {
            return $this->transformPaginator( $data, $transformer );
        }

        throw new InvalidArgumentException( 'Data must be one or multiple models implementing the Transformable contract.' );
    }

    /**
     * Serializes the data.
     *
     * @param  ResourceInterface $resource
     * @return array
     */
    public function serialize( ResourceInterface $resource ):array
    {
        $manager = app( Manager::class );

        $data = $resource->getData();
        $model = $data instanceof Collection ? $this->resolveModel( $data ) : $data;

        if ( ! is_null( $data ) ) {
            $transformer = $model::transformer();
            $includes = ( new $transformer( $model ) )->getAvailableIncludes();
            $manager = $manager->parseIncludes( $includes );
        }

        return $manager->createData( $resource )->toArray();
    }

    /**
     * Transform a transformable Eloquent model.
     *
     * @param  Transformable    $model
     * @param  Transformer|null $transformer
     * @return ResourceInterface
     */
    protected function transformModel( Transformable $model, Transformer $transformer = null ):ResourceInterface
    {
        $transformer = $transformer ?: $model::transformer();

        if ( is_null( $transformer ) ) {
            return new FractalNull();
        }

        return $this->transformData( $model, new $transformer( $model ), $model->getTable() );
    }

    /**
     * Transform a collection of Eloquent models.
     *
     * @param  Collection       $collection
     * @param  Transformer|null $transformer
     * @return ResourceInterface
     */
    protected function transformCollection( Collection $collection, Transformer $transformer = null ):ResourceInterface
    {
        $model = $this->resolveModel( $collection );
        $transformer = $transformer ?: $model::transformer();

        if ( is_null( $transformer ) ) {
            return new FractalNull();
        }

        return $this->transformData( $collection, new $transformer( $model ), $model->getTable() );
    }

    /**
     * Transform paginated data using Laravel's paginator.
     *
     * @param LengthAwarePaginator $paginator
     * @param Transformer|null     $transformer
     * @return ResourceInterface
     */
    protected function transformPaginator( LengthAwarePaginator $paginator, Transformer $transformer = null ):ResourceInterface
    {
        $resource = $this->transformCollection( $paginator->getCollection(), $transformer );
        $resource->setPaginator( new IlluminatePaginatorAdapter( $paginator ) );

        return $resource;
    }

    /**
     * Transform the data using the given transformer.
     *
     * @param  Transformable|Collection $data
     * @param  Transformer|null         $transformer
     * @param  string                   $resourceKey
     * @return ResourceInterface
     */
    protected function transformData( $data, Transformer $transformer, string $resourceKey ):ResourceInterface
    {
        $class = $data instanceof Transformable ? FractalItem::class : FractalCollection::class;
        $resource = new $class( $data, $transformer );
        $resource->setResourceKey( $resourceKey );

        return $resource;
    }

    /**
     * Resolves model class path from a collection of models.
     *
     * @param  Collection $collection
     * @return Transformable
     * @throws InvalidArgumentException
     */
    protected function resolveModel( Collection $collection ):Transformable
    {
        $class = $collection->first();

        if ( ! $class instanceof Transformable ) {
            throw new InvalidArgumentException( 'Data must only contain models implementing the Transformable contract.' );
        }

        $collection->each( function ( $model ) use ( $class ) {
            if ( get_class( $model ) !== get_class( $class ) ) {
                throw new InvalidArgumentException( 'You cannot transform arrays or collections with multiple model types.' );
            }
        } );

        return $class;
    }

    /**
     * Here we prepend a status code to the response data, if status code is enabled in
     * the configuration file.
     *
     * @param  int   $statusCode
     * @param  array $data
     * @return array
     */
    protected function includeStatusCode( int $statusCode, array $data ):array
    {
        if ( ! config( 'responder.status_code' ) ) {
            return $data;
        }

        return array_merge( [
            'status' => $statusCode
        ], $data );
    }

    /**
     * Get the skeleton for an error response.
     *
     * @param string $errorCode
     * @param int    $statusCode
     * @return array
     */
    protected function getErrorResponse( string $errorCode, int $statusCode ):array
    {
        $response = [
            'success' => false,
            'error' => [
                'code' => $errorCode
            ]
        ];

        return $this->includeStatusCode( $statusCode, $response );
    }

    /**
     * Get any error messages for the response. If no message can be found it will
     * try to resolve a set message from the translator.
     *
     * @param  string $errorCode
     * @param  mixed  $message
     * @return array
     */
    protected function getErrorMessages( string $errorCode, $message ):array
    {
        if ( is_array( $message ) ) {
            return $message;

        } elseif ( is_string( $message ) ) {
            if ( strlen( $message ) === 0 ) {
                return [ ];
            }

            return [ $message ];
        }

        return [ app( 'translator' )->trans( 'errors.' . $errorCode ) ];
    }
}