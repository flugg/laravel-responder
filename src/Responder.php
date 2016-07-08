<?php

namespace Mangopixel\Responder;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use League\Fractal\Resource\Collection as FractalCollection;
use League\Fractal\Resource\Item as FractalItem;
use League\Fractal\Resource\NullResource as FractalNull;
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
            $statusCode = $data;
            $data = null;
        }

        if ( is_null( $data ) ) {
            return response()->json( $this->transform( $data, $model::transformer() ), $statusCode );
        }

        $model = $this->resolveModel( is_array( $data ) ? collect( $data ) : $data );

        return response()->json( $this->transform( $data, $model::transformer() ), $statusCode );
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
     * @return string
     * @throws InvalidArgumentException
     */
    protected function resolveModel( $data ):string
    {
        if ( $data instanceof Transformable ) {
            return get_class( $data );
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
     * @return string
     * @throws InvalidArgumentException
     */
    protected function resolveModelFromCollection( Collection $collection ):string
    {
        $first = $collection->first();
        if ( ! $first instanceof Transformable ) {
            throw new InvalidArgumentException( 'Data must only contain models implementing the Transformable contract.' );
        }

        $class = get_class( $first );
        $collection->each( function ( $model ) use ( $class ) {
            if ( get_class( $model ) !== $class ) {
                throw new InvalidArgumentException( 'You cannot transform arrays or collections with multiple model types.' );
            }
        } );

        return $class;
    }

    /**
     * Transforms and serializes the data using Fractal.
     *
     * @param  mixed  $data
     * @param  string $transformer
     * @param  int    $statusCode
     * @return array
     */
    protected function transform( $data = null, $transformer = null, int $statusCode = 200 ):array
    {
        if ( is_null( $data ) ) {
            $class = FractalNull::class;
            $resource = new $class( $data );

        } else {
            $class = $data instanceof Transformable ? FractalItem::class : FractalCollection::class;
            $resource = new $class( $data, new $transformer );
        }

        $serializedData = app( Manager::class )->createData( $resource )->toArray();

        return [ 'status' => $statusCode ] + $serializedData;
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