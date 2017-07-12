<?php

namespace Flugg\Responder;

use Flugg\Responder\Contracts\TransformFactory;
use Flugg\Responder\Exceptions\InvalidSerializerException;
use Flugg\Responder\Resources\ResourceBuilder;
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
     * A builder for building Fractal resources.
     *
     * @var \Flugg\Responder\ResourceBuilder
     */
    protected $resourceBuilder;

    /**
     * A factory for making transformed arrays.
     *
     * @var \Flugg\Responder\FractalTransformFactory
     */
    private $factory;

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
     * Construct the builder class.
     *
     * @param \Flugg\Responder\Resources\ResourceBuilder  $resourceBuilder
     * @param \Flugg\Responder\Contracts\TransformFactory $factory
     */
    public function __construct(ResourceBuilder $resourceBuilder, TransformFactory $factory)
    {
        $this->resourceBuilder = $resourceBuilder;
        $this->factory = $factory;
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
        $this->resourceBuilder->make($data, $transformer)->withResourceKey($resourceKey);

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
        $this->resourceBuilder->withPaginator($paginator);

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
        $this->resourceBuilder->withCursor($cursor);

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
     * Add meta data appended to the response data.
     *
     * @param  array $meta
     * @return self
     */
    public function meta(array $meta): TransformBuilder
    {
        $this->resourceBuilder->withMeta($meta);

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
        $resource = $this->resourceBuilder->get();

        $this->with($relations = $resource->getTransformer()->extractDefaultRelations());

        $data = $resource->getData();
        if ($data instanceof Model || $data instanceof Collection) {
            $data->load($relations);
        }

        $with = collect($this->with)->map(function ($value, $key) {
            return is_numeric($key) ? $value : $key;
        })->values();

        return $this->factory->make($resource, $this->serializer, $with, $this->without);
    }
}