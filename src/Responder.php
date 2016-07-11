<?php

namespace Mangopixel\Responder;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use League\Fractal\Resource\Collection as FractalCollection;
use League\Fractal\Resource\Item as FractalItem;
use League\Fractal\Resource\NullResource as FractalNull;
use League\Fractal\Resource\ResourceInterface;
use Mangopixel\Responder\Contracts\Manager;
use Mangopixel\Responder\Contracts\Responder as ResponderContract;
use Mangopixel\Responder\Contracts\Transformable;

/**
 * The responder service. This class is responsible for generating the API responses.
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
     * Resolves model class path from the data.
     *
     * @param  mixed $data
     * @return Transformable
     * @throws InvalidArgumentException
     */
    protected function resolveModel( $data ):Transformable
    {
        if ( is_null( $data ) ) {
            return null;
        } elseif ( $data instanceof Transformable ) {
            return $data;
        } elseif ( $data instanceof Collection ) {
            return $this->resolveModelFromCollection( $data );
        } else {
            throw new InvalidArgumentException( 'Data must be one or multiple models implementing the Transformable contract.' );
        }
    }

    /**
     * Resolves model class path from a collection of models.
     *
     * @param  Collection $collection
     * @return Transformable
     * @throws InvalidArgumentException
     */
    protected function resolveModelFromCollection( Collection $collection ):Transformable
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
     * Transforms the data using Fractal.
     *
     * @param  mixed  $data
     * @param  string $transformer
     * @param  int    $statusCode
     * @return ResourceInterface
     */
    public function transform( $data = null, TransformerAbstract $transformer = null ):ResourceInterface
    {
        if ( is_null( $data ) ) {
            return new FractalNull( $data );
        }

        $model = $this->resolveModel( $data );
        if ( is_null( $transformer ) ) {
            $transformer = $model::transformer();
        }

        $class = $data instanceof Transformable ? FractalItem::class : FractalCollection::class;
        $resource = new $class( $data, new $transformer( $model ) );
        $resource->setResourceKey( $model->getTable() );

        return $resource;
    }

    public function serialize( ResourceInterface $resource ):array
    {
        $model = $this->resolveModel( $resource->getData() );
        $transformer = $model::transformer();
        $includes = ( new $transformer( $model ) )->getAvailableIncludes();

        return app( Manager::class )->parseIncludes( $includes )->createData( $resource )->toArray();
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