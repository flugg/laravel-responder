<?php

namespace Mangopixel\Adjuster;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use LogicException;
use Mangopixel\Responder\Exceptions\TransformerNotFoundException;

/**
 *
 *
 * @package Laravel Responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
trait Responder
{
    /**
     *
     *
     * @param  mixed $data
     * @param  int   $statusCode
     * @return JsonResponse
     */
    public function successResponse( $data, int $statusCode = 200 ):JsonResponse
    {
        if ( is_array( $data ) ) {
            $data = collect( $data );
        }

        $model = $this->resolveModel( $data );
        $transformer = $this->getTransformer( $model );

        return response()->json( $this->transform( $data, $transformer ), $statusCode );
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
        if ( $data instanceof EloquentCollection ) {
            return $this->resolveModelFromCollection( $data );
        } elseif ( $data instanceof Model ) {
            return get_class( $data );
        } else {
            throw new InvalidArgumentException( 'Data must be an Eloquent model, an Eloquent collection or an array.' );
        }
    }

    /**
     *
     *
     * @param  EloquentCollection $collection
     * @return string
     * @throws LogicException
     */
    protected function resolveModelFromCollection( EloquentCollection $collection ):string
    {
        $class = $collection->first();

        $collection->each( function ( $model ) use ( $class ) {
            if ( get_class( $model ) !== $class ) {
                throw new LogicException( 'You cannot transform arrays or collections with multiple model types.' );
            }
        } );

        return $class;
    }

    /**
     *
     *
     * @param  string $key
     * @return string
     * @throws TransformerNotFoundException
     */
    protected function getTransformer( string $key ):string
    {
        $transformers = app( ResponderServiceProvider::class )->getTransformers();

        if ( ! array_has( $transformers, $key ) ) {
            throw new TransformerNotFoundException( "No transformer mapping for $key could be found." );
        }

        return $transformers[ $key ];
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
        $class = $data instanceof Model ? Item::class : Collection::class;
        $resource = new $class( $data->toArray(), new $transformer );

        return app( 'responder.fractal' )->createData( $resource )->toArray();
    }
}