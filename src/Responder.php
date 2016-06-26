<?php

namespace Mangopixel\Responder;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use League\Fractal\Resource\Collection as FractalCollection;
use League\Fractal\Resource\Item as FractalItem;
use Mangopixel\Responder\Contracts\Manageable;
use Mangopixel\Responder\Contracts\Respondable;
use Mangopixel\Responder\Contracts\Transformable;

/**
 *
 *
 * @package Laravel Responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class Responder implements Respondable
{
    /**
     *
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
     *
     *
     * @param  string $error
     * @param  int    $statusCode
     * @return JsonResponse
     */
    public function error( string $error, int $statusCode = 404 ):JsonResponse
    {
        return response()->json( [
            'error' => $error,
            'status' => $statusCode
        ], 404 );
    }

    /**
     *
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
     *
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
     *
     *
     * @param  mixed  $data
     * @param  string $transformer
     * @return array
     */
    protected function transform( $data, string $transformer ):array
    {
        $class = $data instanceof Transformable ? FractalItem::class : FractalCollection::class;
        $resource = new $class( $data, new $transformer );

        return app( Manageable::class )->createData( $resource )->toArray();
    }
}