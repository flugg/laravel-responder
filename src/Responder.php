<?php

namespace Mangopixel\Responder;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
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
    public function generateResponse( $data, int $statusCode ):JsonResponse
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
     * @param  mixed $data
     * @return string
     * @throws InvalidArgumentException
     */
    protected function resolveModel( $data ):string
    {
        if ( $data instanceof Transformable ) {
            return get_class( $data );
        } elseif ( $data instanceof EloquentCollection ) {
            return $this->resolveModelFromCollection( $data );
        } else {
            throw new InvalidArgumentException( 'Data must be one or multiple models implementing the Transformable contract.' );
        }
    }

    /**
     *
     *
     * @param  EloquentCollection $collection
     * @return string
     * @throws InvalidArgumentException
     */
    protected function resolveModelFromCollection( EloquentCollection $collection ):string
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
        $class = $data instanceof Transformable ? Item::class : Collection::class;
        $resource = new $class( $data, new $transformer );

        return app( 'responder.fractal' )->createData( $resource )->toArray();
    }
}