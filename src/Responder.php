<?php

namespace Flugg\Responder;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection as FractalCollection;
use League\Fractal\Resource\Item as FractalItem;
use League\Fractal\Resource\NullResource as FractalNull;
use League\Fractal\Resource\ResourceInterface;
use Flugg\Responder\Contracts\Manager;
use Flugg\Responder\Contracts\Responder as ResponderContract;
use Flugg\Responder\Contracts\Transformable;

/**
 * The responder service. This class is responsible for generating JSON API responses.
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
     * @param  mixed $data
     * @param  int   $statusCode
     * @return JsonResponse
     */
    public function success( $data = null, int $statusCode = 200 ):JsonResponse
    {
        if ( is_integer( $data ) ) {
            list( $statusCode, $data ) = [ $data, null ];
        }

        $resource = $this->transform( $data );
        $data = $this->serialize( $resource );

        if ( config( 'responder.status_code' ) ) {
            $data = array_merge( [
                'status' => $statusCode
            ], $data );
        }

        return response()->json( $data, $statusCode );
    }

    /**
     * Generate an error JSON response.
     *
     * @param  string $errorCode
     * @param  int    $statusCode
     * @param  mixed  $message
     * @return JsonResponse
     */
    public function error( string $errorCode, int $statusCode = 500, $message = null ):JsonResponse
    {
        $response = $this->getErrorResponse( $errorCode, $statusCode );
        $messages = $this->getErrorMessages( $message, $errorCode );

        if ( count( $messages ) === 1 ) {
            $response[ 'error' ][ 'message' ] = $messages[ 0 ];
        } else if ( count( $messages ) > 1 ) {
            $response[ 'error' ][ 'messages' ] = $messages;
        }

        return response()->json( $response, $statusCode );
    }

    /**
     * Transforms the data using Fractal.
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
        } elseif ( $data instanceof LengthAwarePaginator ) {
            return $this->transformPaginator( $data, $transformer );
        }

        throw new InvalidArgumentException( 'Data must be one or multiple models implementing the Transformable contract.' );
    }

    /**
     * Transform a transformable Eloquent model.
     *
     * @param  Transformable    $model
     * @param  Transformer|null $transformer
     * @return FractalItem
     */
    protected function transformModel( Transformable $model, Transformer $transformer = null ):FractalItem
    {
        $transformer = $transformer ?: $model::transformer();

        return $this->transformData( $model, new $transformer( $model ), $model->getTable() );
    }

    /**
     * Transform a collection of Eloquent models.
     *
     * @param  Collection       $collection
     * @param  Transformer|null $transformer
     * @return FractalCollection
     */
    protected function transformCollection( Collection $collection, Transformer $transformer = null ):FractalCollection
    {
        $model = $this->resolveModel( $collection );
        $transformer = $transformer ?: $model::transformer();

        return $this->transformData( $collection, new $transformer( $model ), $model->getTable() );
    }

    /**
     * Transform paginated data using Laravel's paginator.
     *
     * @param LengthAwarePaginator $paginator
     * @param Transformer|null     $transformer
     * @return FractalCollection
     */
    protected function transformPaginator( LengthAwarePaginator $paginator, Transformer $transformer = null ):FractalCollection
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
     * Get the skeleton for an error response.
     *
     * @param string $errorCode
     * @param int    $statusCode
     * @return array
     */
    protected function getErrorResponse( string $errorCode, int $statusCode ):array
    {
        return [
            'success' => false,
            'status' => $statusCode,
            'error' => [
                'code' => $errorCode
            ]
        ];
    }

    /**
     * Get any error messages for the response. If no message can be found it will
     * try to resolve a set message from the translator.
     *
     * @param  mixed  $message
     * @param  string $errorCode
     * @return array
     */
    protected function getErrorMessages( $message, string $errorCode ):array
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