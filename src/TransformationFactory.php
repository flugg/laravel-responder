<?php

namespace Flugg\Responder;

use Flugg\Responder\Contracts\Transformable;
use Flugg\Responder\Exceptions\InvalidTransformerException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use League\Fractal\Manager;
use League\Fractal\Resource\NullResource;
use League\Fractal\Resource\ResourceInterface;

/**
 * This class is responsible for building a transformation object from
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class TransformationFactory
{
    /**
     * The Fractal manager used for transforming and serializing data.
     *
     * @var \League\Fractal\Manager
     */
    protected $manager;

    /**
     * The resource factory used to generate resource instances.
     *
     * @var \Flugg\Responder\ResourceFactory
     */
    protected $resourceFactory;

    /**
     * Construct the factory.
     *
     * @param \League\Fractal\Manager          $manager
     * @param \Flugg\Responder\ResourceFactory $resourceFactory
     */
    public function __construct(Manager $manager, ResourceFactory $resourceFactory)
    {
        $this->manager = $manager;
        $this->resourceFactory = $resourceFactory;
    }

    /**
     * A factory method to make a transformation object from the given data.
     *
     * @param  mixed                                             $data
     * @param  \Flugg\Responder\Transformer|callable|string|null $transformer
     * @param  string|null                                       $resourceKey
     * @return \Flugg\Responder\Transformation
     */
    public function make($data = null, $transformer = null, string $resourceKey = null): Transformation
    {
        $resource = $this->resourceFactory->make($data);

        if ($resource instanceof NullResource) {
            return $this->makeEmpty($resource);
        }

        if (! is_null($model = $this->resolveModel($resource->getData()))) {
            return $this->makeWithModel($resource, $model, $transformer, $resourceKey);
        }

        return $this->makeWithoutModel($resource, $transformer, $resourceKey);
    }

    /**
     * Make an empty transformation with no model, transformer or resource key.
     *
     * @param  \League\Fractal\Resource\ResourceInterface $resource
     * @return \Flugg\Responder\Transformation
     */
    protected function makeEmpty(ResourceInterface $resource): Transformation
    {
        return new Transformation($this->manager, $resource);
    }

    /**
     * Make a transformation with data containing an Eloquent model.
     *
     * @param  \League\Fractal\Resource\ResourceInterface        $resource
     * @param  \Illuminate\Database\Eloquent\Model               $model
     * @param  \Flugg\Responder\Transformer|callable|string|null $transformer
     * @param  string|null                                       $resourceKey
     * @return \Flugg\Responder\Transformation
     */
    protected function makeWithModel(ResourceInterface $resource, Model $model, $transformer = null, string $resourceKey = null): Transformation
    {
        $resource->setTransformer($this->parseTransformer($transformer ?: $this->resolveTransformer($model)));
        $resource->setResourceKey($resourceKey ?: $this->resolveResourceKey($model));

        return new Transformation($this->manager, $resource, $model);
    }

    /**
     * Make a transformation with raw data.
     *
     * @param \League\Fractal\Resource\ResourceInterface         $resource
     * @param  \Flugg\Responder\Transformer|callable|string|null $transformer
     * @param  string|null                                       $resourceKey
     * @return \Flugg\Responder\Transformation
     */
    protected function makeWithoutModel(ResourceInterface $resource, $transformer = null, string $resourceKey = null):Transformation
    {
        $data = $resource->getData() instanceof Collection ? $resource->getData()->toArray() : $resource->getData();
        $resource->setTransformer(is_null($transformer) ? $this->makeTransformer($data) : $this->parseTransformer($transformer));

        if (! is_null($resourceKey)) {
            $resource->setResourceKey($resourceKey);
        }

        return new Transformation($this->manager, $resource);
    }

    /**
     * Resolve model from the given data.
     *
     * @param  mixed $data
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    protected function resolveModel($data)
    {
        if ($data instanceof Model) {
            return $data;
        }

        $data = collect($data);

        return ! $data->isEmpty() && $data->first() instanceof Model ? $data->first() : null;
    }

    /**
     * Resolve a transformer from the model. If no transformer is found, we will make a
     * closure based one.
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @return \Flugg\Responder\Transformer|callable|string
     */
    protected function resolveTransformer(Model $model)
    {
        if ($model instanceof Transformable) {
            return $model::transformer();
        }

        return $this->makeTransformer($model->toArray());
    }

    /**
     * Build a closure based transformer from the given array.
     *
     * @param  array $data
     * @return callable
     */
    protected function makeTransformer(array $data):callable
    {
        return function () use ($data) {
            return $data;
        };
    }

    /**
     * Parse a transformer object or string.
     *
     * @param  \Flugg\Responder\Transformer|callable|string $transformer
     * @return \Flugg\Responder\Transformer|callable
     * @throws \Flugg\Responder\Exceptions\InvalidTransformerException
     */
    protected function parseTransformer($transformer)
    {
        if (is_string($transformer)) {
            $transformer = resolve($transformer);
        }

        if (! is_callable($transformer) && ! $transformer instanceof Transformer) {
            throw new InvalidTransformerException();
        }

        return $transformer;
    }

    /**
     * Resolve a resource key from the model.
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @return string
     */
    protected function resolveResourceKey(Model $model):string
    {
        if (method_exists($model, 'getResourceKey')) {
            return $model->getResourceKey();
        }

        return $model->getTable();
    }
}