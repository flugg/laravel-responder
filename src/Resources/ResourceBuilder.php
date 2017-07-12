<?php

namespace Flugg\Responder\Resources;

use Flugg\Responder\Exceptions\InvalidTransformerException;
use Flugg\Responder\Transformers\Transformer;
use Flugg\Responder\Transformers\TransformerManager;
use League\Fractal\Pagination\Cursor;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\NullResource;
use League\Fractal\Resource\ResourceInterface;

/**
 * This class is responsible for making Fractal resources from a variety of data types.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class ResourceBuilder
{
    /**
     * A factory class used to initialize Fractal resources.
     *
     * @var \Flugg\Responder\ResourceFactory
     */
    protected $resourceFactory;

    /**
     * A manager class responsible for handling transformers.
     *
     * @var \Flugg\Responder\TransformerManager
     */
    protected $transformerManager;

    /**
     * The resource that's being built.
     *
     * @var \League\Fractal\Resource\ResourceInterface
     */
    protected $resource;

    /**
     * Construct the resource factory.
     *
     * @param \Flugg\Responder\Resources\ResourceFactory       $resourceFactory
     * @param \Flugg\Responder\Transformers\TransformerManager $transformerManager
     */
    public function __construct(ResourceFactory $resourceFactory, TransformerManager $transformerManager)
    {
        $this->transformerManager = $transformerManager;
        $this->resourceFactory = $resourceFactory;
    }

    /**
     * Make resource from the given data and transformer.
     *
     * @param  null                                                           $data
     * @param  \Flugg\Responder\Transformers\Transformer|string|callable|null $transformer
     * @return self
     */
    public function make($data = null, $transformer = null): ResourceBuilder
    {
        $this->resource = $this->resourceFactory->make($data);

        if (! $this->resource instanceof NullResource) {
            $this->transformer($transformer ?: $this->transformerManager->findTransformer($data));
        }

        return $this;
    }

    /**
     * Set the resource key on the resource.
     *
     * @param  string|null $resourceKey
     * @return self
     */
    public function withResourceKey(string $resourceKey = null): ResourceBuilder
    {
        $this->resource->setResourceKey($resourceKey);

        return $this;
    }

    /**
     * Add meta data to the resource.
     *
     * @param  array $meta
     * @return self
     */
    public function withMeta(array $meta): ResourceBuilder
    {
        $this->resource->setMeta(array_merge($this->resource->getMeta(), $meta));

        return $this;
    }

    /**
     * Set paginator on the resource.
     *
     * @param  \League\Fractal\Pagination\IlluminatePaginatorAdapter $paginator
     * @return self
     */
    public function withPaginator(IlluminatePaginatorAdapter $paginator): ResourceBuilder
    {
        $this->resource->setPaginator($paginator);

        return $this;
    }

    /**
     * Set cursor on the resource.
     *
     * @param  \League\Fractal\Pagination\Cursor $cursor
     * @return self
     */
    public function withCursor(Cursor $cursor): ResourceBuilder
    {
        $this->resource->setCursor($cursor);

        return $this;
    }

    /**
     * Retrieve the built resource.
     *
     * @return \League\Fractal\Resource\ResourceInterface
     */
    public function get(): ResourceInterface
    {
        return $this->resource;
    }

    /**
     * Set the serializer.
     *
     * @param  \Flugg\Responder\Transformers\Transformer|callable $transformer
     * @return self
     * @throws \Flugg\Responder\Exceptions\InvalidTransformerException
     */
    public function withTransformer($transformer): ResourceBuilder
    {
        if (is_string($transformer)) {
            $transformer = $this->transformerManager->resolve($transformer);
        }

        if (! is_callable($transformer) && ! $transformer instanceof Transformer) {
            throw new InvalidTransformerException;
        }

        $this->resource->setTransformer($transformer);

        return $this;
    }
}