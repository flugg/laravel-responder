<?php

namespace Flugg\Responder;

use Flugg\Responder\Contracts\TransformFactory;
use Flugg\Responder\Exceptions\InvalidSerializerException;
use Flugg\Responder\Pagination\CursorPaginator;
use Flugg\Responder\Pagination\PaginatorFactory;
use Flugg\Responder\Resources\ResourceFactory;
use Flugg\Responder\Transformers\Transformer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use League\Fractal\Pagination\Cursor;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Serializer\SerializerAbstract;

/**
 * A builder class responsible for building transformed arrays.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class TransformBuilder
{
    /**
     * A factory class for making Fractal resources.
     *
     * @var \Flugg\Responder\ResourceFactory
     */
    protected $resourceFactory;

    /**
     * A factory for making transformed arrays.
     *
     * @var \Flugg\Responder\FractalTransformFactory
     */
    private $transformFactory;

    /**
     * A factory used to build Fractal paginator adapters.
     *
     * @var \Flugg\Responder\Pagination\PaginatorFactory
     */
    protected $paginatorFactory;

    /**
     * The resource that's being built.
     *
     * @var \League\Fractal\Resource\ResourceInterface
     */
    protected $resource;

    /**
     * A serializer for formatting data after transforming.
     *
     * @var \League\Fractal\Serializer\SerializerAbstract
     */
    protected $serializer;

    /**
     * A list of included relations.
     *
     * @var array
     */
    protected $with = [];

    /**
     * A list of excluded relations.
     *
     * @var array
     */
    protected $without = [];

    /**
     * A list of sparse fieldsets.
     *
     * @var array
     */
    protected $only = [];

    /**
     * Construct the builder class.
     *
     * @param \Flugg\Responder\Resources\ResourceFactory   $resourceFactory
     * @param \Flugg\Responder\Contracts\TransformFactory  $transformFactory
     * @param \Flugg\Responder\Pagination\PaginatorFactory $paginatorFactory
     */
    public function __construct(ResourceFactory $resourceFactory, TransformFactory $transformFactory, PaginatorFactory $paginatorFactory)
    {
        $this->resourceFactory = $resourceFactory;
        $this->transformFactory = $transformFactory;
        $this->paginatorFactory = $paginatorFactory;
        $this->resource = $this->resourceFactory->make();
    }

    /**
     * Make a resource from the given data and transformer and set the resource key.
     *
     * @param  mixed                                                          $data
     * @param  \Flugg\Responder\Transformers\Transformer|callable|string|null $transformer
     * @param  string|null                                                    $resourceKey
     * @return self
     */
    public function resource($data = null, $transformer = null, string $resourceKey = null): TransformBuilder
    {
        $this->resource = $this->resourceFactory->make($data, $transformer, $resourceKey);

        if ($data instanceof CursorPaginator) {
            $this->cursor($this->paginatorFactory->makeCursor($data));
        } elseif ($data instanceof LengthAwarePaginator) {
            $this->paginator($this->paginatorFactory->make($data));
        }

        return $this;
    }

    /**
     * Manually set the cursor on the resource.
     *
     * @param  \League\Fractal\Pagination\Cursor $cursor
     * @return self
     */
    public function cursor(Cursor $cursor): TransformBuilder
    {
        $this->resource->setCursor($cursor);

        return $this;
    }

    /**
     * Manually set the paginator on the resource.
     *
     * @param  \League\Fractal\Pagination\IlluminatePaginatorAdapter $paginator
     * @return self
     */
    public function paginator(IlluminatePaginatorAdapter $paginator): TransformBuilder
    {
        $this->resource->setPaginator($paginator);

        return $this;
    }

    /**
     * Add meta data appended to the response data.
     *
     * @param  array $meta
     * @return self
     */
    public function meta(array $meta): TransformBuilder
    {
        $this->resource->setMeta($meta);

        return $this;
    }

    /**
     * Include relations to the transform.
     *
     * @param  string[]|string $relations
     * @return self
     */
    public function with($relations): TransformBuilder
    {
        $this->with = array_merge($this->with, is_array($relations) ? $relations : func_get_args());

        return $this;
    }

    /**
     * Exclude relations from the transform.
     *
     * @param  string[]|string $relations
     * @return self
     */
    public function without($relations): TransformBuilder
    {
        $this->without = array_merge($this->without, is_array($relations) ? $relations : func_get_args());

        return $this;
    }

    /**
     * Filter fields to output using sparse fieldsets.
     *
     * @param  string[]|string $fields
     * @return self
     */
    public function only($fields): TransformBuilder
    {
        $this->only = array_merge($this->only, is_array($fields) ? $fields : func_get_args());

        return $this;
    }

    /**
     * Set the serializer.
     *
     * @param  \League\Fractal\Serializer\SerializerAbstract|string $serializer
     * @return self
     * @throws \Flugg\Responder\Exceptions\InvalidSerializerException
     */
    public function serializer($serializer): TransformBuilder
    {
        if (is_string($serializer)) {
            $serializer = new $serializer;
        }

        if (! $serializer instanceof SerializerAbstract) {
            throw new InvalidSerializerException;
        }

        $this->serializer = $serializer;

        return $this;
    }

    /**
     * Transform and serialize the data and return the transformed array.
     *
     * @return array
     */
    public function transform(): array
    {
        $this->prepareRelations();

        return $this->transformFactory->make($this->resource, $this->serializer, [
            'includes' => $this->with,
            'excludes' => $this->without,
            'fields' => $this->only,
        ]);
    }

    /**
     * Prepare requested relations for the transformation.
     *
     * @return void
     */
    protected function prepareRelations()
    {
        $this->setDefaultIncludes($this->resource->getTransformer());
        $this->eagerLoadIfApplicable($this->resource->getData());

        $this->with = $this->trimEagerLoadFunctions($this->with);
    }

    /**
     * Set default includes extracted from the transformer.
     *
     * @param \Flugg\Responder\Transformers\Transformer|callable $transformer
     * @return void
     */
    protected function setDefaultIncludes($transformer)
    {
        if ($transformer instanceof Transformer) {
            $this->with($transformer->extractDefaultRelations());
        }
    }

    /**
     * Eager load relations on the given data, if it's an Eloquent model or collection.
     *
     * @param  mixed $data
     * @return void
     */
    protected function eagerLoadIfApplicable($data)
    {
        if ($data instanceof Model || $data instanceof Collection) {
            $data->load($this->with);
        }
    }

    /**
     * Remove eager load constraint functions from the given array.
     *
     * @param  array $relations
     * @return void
     */
    protected function trimEagerLoadFunctions(array $relations)
    {
        return collect($relations)->map(function ($value, $key) {
            return is_numeric($key) ? $value : $key;
        })->values()->all();
    }
}