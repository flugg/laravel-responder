<?php

namespace Mangopixel\Responder;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use League\Fractal\Resource\Collection as FractalCollection;
use League\Fractal\Resource\Item as FractalItem;
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
    public function success( $data, int $statusCode = 200 ):JsonResponse
    {
        if ( is_array( $data ) ) {
            $data = collect( $data );
        }

        $model = $this->resolveModel( $data );

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
    public function error( string $errorCode, int $statusCode = 404, $message = null ):JsonResponse
    {
        $response = $this->getErrorResponse( $errorCode, $statusCode );
        $messages = $this->getErrorMessages( $message, $errorCode );

        if ( count( $messages ) === 1 ) {
            $response[ 'error' ][ 'message' ] = $messages;
        } else if ( count( $messages ) > 1 ) {
            $response[ 'error' ][ 'messages' ] = $messages;
        }

        return response()->json( $response );
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
     * @return array
     */
    protected function transform( $data, string $transformer ):array
    {
        $class = $data instanceof Transformable ? FractalItem::class : FractalCollection::class;
        $resource = new $class( $data, new $transformer );

        return app( Manager::class )->createData( $resource )->toArray();
    }

    /**
     * Get the skeleton for an error response.
     *
     * @param string $errorCode
     * @param int    $statusCode
     * @return array
     */
    private function getErrorResponse( string $errorCode, int $statusCode ):array
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
     * Set an error message field to an existing response array. If message is an array,
     * we will set the field 'messages' instead of 'message'. If no messages can be
     * found, we will not set any fields to the response array.
     *
     * @param  mixed  $message
     * @param  string $errorCode
     * @return array
     */
    private function getErrorMessages( $message, string $errorCode ):array
    {
        $translator = app( 'translator' );

        if ( is_array( $message ) ) {
            return $message;

        } elseif ( is_string( $message ) && strlen( $message ) ) {
            return [ $message ];

        } elseif ( $translator->has( $key = 'errors.' . $errorCode ) ) {
            return [ $translator->trans( $key ) ];
        }

        return [ ];
    }
}