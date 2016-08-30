<?php

namespace Flugg\Responder\Http;

use Flugg\Responder\Contracts\Transformable;
use Flugg\Responder\Exceptions\InvalidSerializerException;
use Flugg\Responder\Exceptions\InvalidTransformerException;
use Flugg\Responder\Exceptions\SerializerNotFoundException;
use Flugg\Responder\ResourceFactory;
use Flugg\Responder\ResourceResolver;
use Flugg\Responder\Transformation;
use Flugg\Responder\Transformer;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use League\Fractal\Manager;
use League\Fractal\Resource\ResourceInterface;
use League\Fractal\Serializer\SerializerAbstract;

/**
 * This class is a response builder for building successful JSON API responses and is
 * responsible for transforming and serializing the data.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class SuccessResponseBuilder extends ResponseBuilder
{
    /**
     * The manager responsible for transforming and serializing data.
     *
     * @var \Flugg\Responder\Contracts\Manager
     */
    protected $manager;

    /**
     * The meta data appended to the serialized data.
     *
     * @var array
     */
    protected $meta = [];

    /**
     * The Fractal resource instance containing the data and transformer.
     *
     * @var \League\Fractal\Resource\ResourceInterface
     */
    protected $resource;

    /**
     * The resource factory used to generate resource instances.
     *
     * @var \Flugg\Responder\ResourceFactory
     */
    protected $resourceFactory;

    /**
     * The HTTP status code for the response.
     *
     * @var int
     */
    protected $statusCode = 200;

    /**
     * SuccessResponseBuilder constructor.
     *
     * @param \Illuminate\Contracts\Routing\ResponseFactory $responseFactory
     * @param \Flugg\Responder\ResourceFactory              $resourceFactory
     * @param \League\Fractal\Manager                       $manager
     */
    public function __construct(ResponseFactory $responseFactory, ResourceFactory $resourceFactory, Manager $manager)
    {
        $this->resourceFactory = $resourceFactory;
        $this->manager = $manager;
        $this->resource = $this->resourceFactory->make();

        parent::__construct($responseFactory);
    }

    /**
     * Add data to the meta data appended to the response data.
     *
     * @param  array $data
     * @return self
     */
    public function addMeta(array $data):SuccessResponseBuilder
    {
        $this->meta = array_merge($this->meta, $data);

        return $this;
    }

    /**
     * Set the serializer used to serialize the resource data.
     *
     * @param  \League\Fractal\Serializer\SerializerAbstract|string $serializer
     * @return self
     */
    public function serializer($serializer):SuccessResponseBuilder
    {
        $this->manager->setSerializer($this->resolveSerializer($serializer));

        return $this;
    }

    /**
     * Set the HTTP status code for the response.
     *
     * @param  int $statusCode
     * @return self
     * @throws \InvalidArgumentException
     */
    public function setStatus(int $statusCode):ResponseBuilder
    {
        if ($statusCode < 100 || $statusCode >= 400) {
            throw new InvalidArgumentException("{$statusCode} is not a valid success HTTP status code.");
        }

        return parent::setStatus($statusCode);
    }

    /**
     * Set the transformation data. This will set a new resource instance on the response
     * builder depending on what type of data is provided.
     *
     * @param  mixed|null           $data
     * @param  callable|string|null $transformer
     * @param  string|null          $resourceKey
     * @return self
     */
    public function transform($data = null, $transformer = null, string $resourceKey = null):SuccessResponseBuilder
    {
        $resource = $this->resourceFactory->make($data);

        if (! is_null($resource->getData())) {
            $model = $this->resolveModel($resource->getData());
            $transformer = $this->resolveTransformer($model, $transformer);
            $resourceKey = $this->resolveResourceKey($model, $resourceKey);
        }

        $this->resource = $resource->setTransformer($transformer)->setResourceKey($resourceKey);

        return $this;
    }

    /**
     * Convert the response to an array.
     *
     * @return array
     */
    public function toArray():array
    {
        return $this->serialize($this->getResource());
    }

    /**
     * Get the Fractal resource instance.
     *
     * @return \League\Fractal\Resource\ResourceInterface
     */
    public function getResource():ResourceInterface
    {
        return $this->resource->setMeta($this->meta);
    }

    /**
     * Get the Fractal manager responsible for transforming and serializing the data.
     *
     * @return \League\Fractal\Manager
     */
    public function getManager():Manager
    {
        return $this->manager;
    }

    /**
     * Resolve a serializer instance from the value.
     *
     * @param  \League\Fractal\Serializer\SerializerAbstract|string $serializer
     * @return \League\Fractal\Serializer\SerializerAbstract
     * @throws \Flugg\Responder\Exceptions\InvalidSerializerException
     */
    protected function resolveSerializer($serializer):SerializerAbstract
    {
        if (is_string($serializer)) {
            $serializer = new $serializer;
        }

        if (! $serializer instanceof SerializerAbstract) {
            throw new InvalidSerializerException();
        }

        return $serializer;
    }

    /**
     * Resolve a model instance from the data.
     *
     * @param  \Illuminate\Database\Eloquent\Model|array $data
     * @return \Illuminate\Database\Eloquent\Model
     * @throws \InvalidArgumentException
     */
    protected function resolveModel($data):Model
    {
        if ($data instanceof Model) {
            return $data;
        }

        $model = $data[0];
        if (! $model instanceof Model) {
            throw new InvalidArgumentException('You can only transform data containing Eloquent models.');
        }

        return $model;
    }

    /**
     * Resolve a transformer.
     *
     * @param  \Illuminate\Database\ELoquent\Model        $model
     * @param  \Flugg\Responder\Transformer|callable|null $transformer
     * @return \Flugg\Responder\Transformer|callable
     */
    protected function resolveTransformer(Model $model, $transformer = null)
    {
        $transformer = $transformer ?: $this->resolveTransformerFromModel($model);

        if (is_string($transformer)) {
            $transformer = new $transformer;
        }

        return $this->parseTransformer($transformer, $model);
    }

    /**
     * Resolve a transformer from the model. If the model is not transformable, a closure
     * based transformer will be created instead, from the model's fillable attributes.
     *
     * @param  \Illuminate\Database\ELoquent\Model $model
     * @return \Flugg\Responder\Transformer|callable
     */
    protected function resolveTransformerFromModel(Model $model)
    {
        if (! $model instanceof Transformable) {
            return function () use ($model) {
                return $model->toArray();
            };
        }

        return $model::transformer();
    }

    /**
     * Parse a transformer class and set relations.
     *
     * @param  \Flugg\Responder\Transformer|callable $transformer
     * @param  \Illuminate\Database\ELoquent\Model   $model
     * @return \Flugg\Responder\Transformer|callable
     * @throws \InvalidTransformerException
     */
    protected function parseTransformer($transformer, Model $model)
    {
        if ($transformer instanceof Transformer) {
            $transformer = $transformer->setRelations($this->resolveRelations($model));
            $this->manager->parseIncludes($transformer->getRelations());

        } elseif (! is_callable($transformer)) {
            throw new InvalidTransformerException($model);
        }

        return $transformer;
    }

    /**
     * Resolve eager loaded relations from the model.
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @return array
     */
    protected function resolveRelations(Model $model):array
    {
        return array_keys($model->getRelations());
    }

    /**
     * Resolve the resource key from the model.
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @param  string|null                         $resourceKey
     * @return string
     */
    protected function resolveResourceKey(Model $model, string $resourceKey = null):string
    {
        if (! is_null($resourceKey)) {
            return $resourceKey;
        }

        if (method_exists($model, 'getResourceKey')) {
            return $model->getResourceKey();
        }

        return $model->getTable();
    }

    /**
     * Serialize the transformation data.
     *
     * @param  ResourceInterface $resource
     * @return array
     */
    protected function serialize(ResourceInterface $resource):array
    {
        return $this->manager->createData($resource)->toArray();
    }
}