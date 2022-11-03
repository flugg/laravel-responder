<?php

namespace Flugg\Responder;

use Flugg\Responder\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use League\Fractal\Pagination\Cursor;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection as CollectionResource;
use League\Fractal\Resource\Item as ItemResource;
use League\Fractal\Resource\NullResource;
use League\Fractal\Resource\ResourceInterface;

/**
 * This class builds an instance of [\League\Fractal\Resource\ResourceInterface]. It
 * supports a variety of different types of data, and resolves the correct resource
 * automatically.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class ResourceFactory
{
    /**
     * Mappings of supported data types with corresponding make methods.
     *
     * @var array
     */
    protected $methods = [
        Builder::class => 'makeFromBuilder',
        Collection::class => 'makeFromCollection',
        Pivot::class => 'makeFromPivot',
        Model::class => 'makeFromModel',
        Paginator::class => 'makeFromPaginator',
        CursorPaginator::class => 'makeFromCursor',
        Relation::class => 'makeFromRelation',
    ];

    /**
     * List of given request parameters.
     *
     * @var array
     */
    protected $parameters;

    /**
     * Build a resource instance from the given data.
     *
     * @param  mixed|null $data
     * @param  array      $parameters
     * @return \League\Fractal\Resource\ResourceInterface
     */
    public function make($data = null, array $parameters = [])
    {
        $this->parameters = $parameters;

        if (is_null($data)) {
            return new NullResource();
        } elseif (is_array($data)) {
            return static::makeFromArray($data);
        }

        $method = static::getMakeMethod($data);

        return static::$method($data);
    }

    /**
     * Resolve which make method to call from the given date type.
     *
     * @param  mixed $data
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function getMakeMethod($data):string
    {
        foreach ($this->methods as $class => $method) {
            if ($data instanceof $class) {
                return $method;
            }
        }

        throw new InvalidArgumentException('Given data cannot be transformed.');
    }

    /**
     * Make resource from an Eloquent model.
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @return \League\Fractal\Resource\ResourceInterface
     */
    protected function makeFromModel(Model $model):ResourceInterface
    {
        return new ItemResource($model);
    }

    /**
     * Make resource from a collection of Eloquent models.
     *
     * @param  array $array
     * @return \League\Fractal\Resource\ResourceInterface
     */
    protected function makeFromArray(array $array):ResourceInterface
    {
        return new CollectionResource($array);
    }

    /**
     * Make resource from a collection.
     *
     * @param  \Illuminate\Support\Collection $collection
     * @return \League\Fractal\Resource\ResourceInterface
     */
    protected function makeFromCollection(Collection $collection):ResourceInterface
    {
        return new CollectionResource($collection);
    }

    /**
     * Make resource from an Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \League\Fractal\Resource\ResourceInterface
     */
    protected function makeFromBuilder(Builder $query):ResourceInterface
    {
        return static::makeFromCollection($query->get());
    }

    /**
     * Make resource from an Eloquent paginator.
     *
     * @param  \Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator
     * @return \League\Fractal\Resource\ResourceInterface
     */
    protected function makeFromPaginator(Paginator $paginator):ResourceInterface
    {
        $resource = static::makeFromCollection($paginator->getCollection());

        if ($resource instanceof CollectionResource) {
            $resource->setPaginator(new IlluminatePaginatorAdapter($paginator->appends($this->parameters)));
        }

        return $resource;
    }

    /**
     * Make resource from a cursor paginator.
     *
     * @param  \Flugg\Responder\Pagination\CursorPaginator $paginator
     * @return \League\Fractal\Resource\ResourceInterface
     */
    protected function makeFromCursor(CursorPaginator $paginator):ResourceInterface
    {
        $resource = static::makeFromCollection($paginator->getCollection());

        if ($resource instanceof CollectionResource) {
            $resource->setCursor(new Cursor($paginator->cursor(), null, $paginator->nextCursor(), $paginator->count()));
        }

        return $resource;
    }

    /**
     * Make resource from an Eloquent pivot table.
     *
     * @param  \Illuminate\Database\Eloquent\Relations\Pivot $pivot
     * @return \League\Fractal\Resource\ResourceInterface
     */
    protected function makeFromPivot(Pivot $pivot):ResourceInterface
    {
        return static::makeFromModel($pivot);
    }

    /**
     * Make resource from an Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Relations\Relation $relation
     * @return \League\Fractal\Resource\ResourceInterface
     */
    protected function makeFromRelation(Relation $relation):ResourceInterface
    {
        return static::makeFromCollection($relation->get());
    }
}