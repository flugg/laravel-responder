<?php

namespace Flugg\Responder\Factories;

use Flugg\Responder\Contracts\Manager;
use Flugg\Responder\Contracts\Transformable;
use Flugg\Responder\Transformer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as Paginator;
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
 *
 *
 * @package Laravel Responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class SuccessResponseFactory extends ResponseFactory
{
    /**
     * Generate a successful JSON response.
     *
     * @param  mixed $data
     * @param  int   $statusCode
     * @param  array $meta
     * @return JsonResponse
     */
    public function make( $data = null, $statusCode = 200, $meta = [ ] ):JsonResponse
    {
        $resource = $this->transform( $data );
        $resource->setMeta( $meta );

        $data = $this->serialize( $resource );
        $data = $this->includeStatusCode( $statusCode, $data );

        return parent::make( $data, $statusCode );
    }

    /**
     * Transforms the data.
     *
     * @param  mixed            $data
     * @param  Transformer|null $transformer
     * @return ResourceInterface
     */
    protected function transform( $data = null, Transformer $transformer = null ):ResourceInterface
    {
        if ( is_null( $data ) ) {
            return new FractalNull();
        }

        $transforms = [
            Transformable::class => 'transformModel',
            Collection::class => 'transformCollection',
            Builder::class => 'transformBuilder',
            Paginator::class => 'transformPaginator'
        ];

        foreach ( $transforms as $class => $transform ) {
            if ( $data instanceof $class ) {
                return $this->$transform( $data, $transformer );
            }
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

        if ( is_null( $transformer ) ) {
            return new FractalItem( $model, function () use ( $model ) {
                return $model->toArray();
            } );
        }

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

        if ( is_null( $transformer ) ) {
            return new FractalCollection( $collection, function () use ( $collection ) {
                return $collection->toArray();
            } );
        }

        return $this->transformData( $collection, new $transformer( $model ), $model->getTable() );
    }

    /**
     * Transform an Eloquent builder.
     *
     * @param  Builder          $collection
     * @param  Transformer|null $transformer
     * @return FractalCollection
     */
    protected function transformBuilder( Builder $query, Transformer $transformer = null ):FractalCollection
    {
        return $this->transformCollection( $query->get(), $transformer );
    }

    /**
     * Transform paginated data using Laravel's paginator.
     *
     * @param Paginator        $paginator
     * @param Transformer|null $transformer
     * @return FractalCollection
     */
    protected function transformPaginator( Paginator $paginator, Transformer $transformer = null ):FractalCollection
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
     * Serializes the data.
     *
     * @param  ResourceInterface $resource
     * @return array
     */
    protected function serialize( ResourceInterface $resource ):array
    {
        $manager = app( Manager::class );

        $data = $resource->getData();
        $model = $data instanceof Collection ? $this->resolveModel( $data ) : $data;

        if ( ! is_null( $data ) ) {
            $transformer = $model::transformer();
            $includes = is_string( $transformer ) ? ( new $transformer( $model ) )->getAvailableIncludes() : [ ];
            $manager = $manager->parseIncludes( $includes );
        }

        return $manager->createData( $resource )->toArray();
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
        if ( ! $this->includeStatusCode ) {
            return $data;
        }

        return array_merge( [
            'status' => $statusCode
        ], $data );
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
}