<?php

namespace Flugg\Responder;

use Flugg\Responder\Contracts\Pagination\PaginatorFactory;
use Flugg\Responder\Contracts\Resources\ResourceFactory;
use Flugg\Responder\Contracts\TransformFactory;
use Flugg\Responder\Exceptions\InvalidSuccessSerializerException;
use Flugg\Responder\Pagination\CursorPaginator;
use Flugg\Responder\Transformers\Transformer as BaseTransformer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use League\Fractal\Pagination\Cursor;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection as CollectionResource;
use League\Fractal\Resource\NullResource;
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
     * @var \Flugg\Responder\Contracts\Resources\ResourceFactory
     */
    protected $resourceFactory;

    /**
     * A factory for making transformed arrays.
     *
     * @var \Flugg\Responder\Contracts\TransformFactory
     */
    private $transformFactory;

    /**
     * A factory used to build Fractal paginator adapters.
     *
     * @var \Flugg\Responder\Contracts\Pagination\PaginatorFactory
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
     * @param \Flugg\Responder\Contracts\Resources\ResourceFactory   $resourceFactory
     * @param \Flugg\Responder\Contracts\TransformFactory            $transformFactory
     * @param \Flugg\Responder\Contracts\Pagination\PaginatorFactory $paginatorFactory
     */
    public function __construct(ResourceFactory $resourceFactory, TransformFactory $transformFactory, PaginatorFactory $paginatorFactory)
    {
        $this->resourceFactory = $resourceFactory;
        $this->transformFactory = $transformFactory;
        $this->paginatorFactory = $paginatorFactory;
    }

    /**
     * Make a resource from the given data and transformer and set the resource key.
     *
     * @param  mixed                                                          $data
     * @param  \Flugg\Responder\Transformers\Transformer|callable|string|null $transformer
     * @param  string|null                                                    $resourceKey
     * @return $this
     */
    public function resource($data = null, $transformer = null, string $resourceKey = null)
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
     * @return $this
     */
    public function cursor(Cursor $cursor)
    {
        if ($this->resource instanceof CollectionResource) {
            $this->resource->setCursor($cursor);
        }

        return $this;
    }

    /**
     * Manually set the paginator on the resource.
     *
     * @param  \League\Fractal\Pagination\IlluminatePaginatorAdapter $paginator
     * @return $this
     */
    public function paginator(IlluminatePaginatorAdapter $paginator)
    {
        if ($this->resource instanceof CollectionResource) {
            $this->resource->setPaginator($paginator);
        }

        return $this;
    }

    /**
     * Add meta data appended to the response data.
     *
     * @param  array $data
     * @return $this
     */
    public function meta(array $data)
    {
        $this->resource->setMeta($data);

        return $this;
    }

    /**
     * Include relations to the transform.
     *
     * @param  string[]|string $relations
     * @return $this
     */
    public function with($relations)
    {
        $this->with = array_merge($this->with, is_array($relations) ? $relations : func_get_args());

        return $this;
    }

    /**
     * Exclude relations from the transform.
     *
     * @param  string[]|string $relations
     * @return $this
     */
    public function without($relations)
    {
        $this->without = array_merge($this->without, is_array($relations) ? $relations : func_get_args());

        return $this;
    }

    /**
     * Filter fields to output using sparse fieldsets.
     *
     * @param  string[]|string $fields
     * @return $this
     */
    public function only($fields)
    {
        $this->only = array_merge($this->only, is_array($fields) ? $fields : func_get_args());

        return $this;
    }

    /**
     * Set the serializer.
     *
     * @param  \League\Fractal\Serializer\SerializerAbstract|string $serializer
     * @return $this
     * @throws \Flugg\Responder\Exceptions\InvalidSuccessSerializerException
     */
    public function serializer($serializer)
    {
        if (is_string($serializer)) {
            $serializer = new $serializer;
        }

        if (! $serializer instanceof SerializerAbstract) {
            throw new InvalidSuccessSerializerException;
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
        $this->prepareRelations($this->resource->getData(), $this->resource->getTransformer());

        return $this->transformFactory->make($this->resource ?: new NullResource, $this->serializer, [
            'includes' => $this->with,
            'excludes' => $this->without,
            'fieldsets' => $this->only,
        ]);
    }

    /**
     * Prepare requested relations for the transformation.
     *
     * @param  mixed                                                          $data
     * @param  \Flugg\Responder\Transformers\Transformer|callable|string|null $transformer
     * @return void
     */
    protected function prepareRelations($data, $transformer)
    {
        if ($transformer instanceof BaseTransformer) {
            $this->includeTransformerRelations($transformer);
        }

        if ($data instanceof Model || $data instanceof Collection) {
            $data->load($this->with);
        }

        $this->with = $this->stripEagerLoadConstraints($this->with);
    }

    /**
     * Include default relationships and add eager load constraints from transformer.
     *
     * @param  \Flugg\Responder\Transformers\Transformer $transformer
     * @return void
     */
    protected function includeTransformerRelations(BaseTransformer $transformer)
    {
        $relations = array_filter(array_keys($this->with), function ($relation) {
            return ! is_numeric($relation);
        });

        $this->with(Collection::make($transformer->defaultRelations())
            ->filter(function ($constrain, $relation) use ($relations) {
                return ! in_array(is_numeric($relation) ? $constrain : $relation, $relations);
            })->all());
    }

    /**
     * Remove eager load constraint functions from the given relations.
     *
     * @param  array $relations
     * @return array
     */
    protected function stripEagerLoadConstraints(array $relations): array
    {
        return collect($relations)->map(function ($value, $key) {
            return is_numeric($key) ? $value : $key;
        })->values()->all();
    }
}