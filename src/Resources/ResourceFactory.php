<?php

namespace Flugg\Responder\Resources;

use Flugg\Responder\Contracts\Resources\ResourceFactory as ResourceFactoryContract;
use Flugg\Responder\Contracts\Resources\ResourceKeyResolver as ResourceKeyResolverContract;
use Flugg\Responder\Contracts\Transformers\TransformerResolver;
use Illuminate\Support\Arr;
use League\Fractal\Resource\Collection as CollectionResource;
use League\Fractal\Resource\Item as ItemResource;
use League\Fractal\Resource\NullResource;
use League\Fractal\Resource\Primitive;
use League\Fractal\Resource\ResourceInterface;
use Traversable;

/**
 * This class is responsible for making Fractal resources from a variety of data types.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class ResourceFactory implements ResourceFactoryContract
{
    /**
     * A service class, used to normalize data.
     *
     * @var \Flugg\Responder\Resources\DataNormalizer
     */
    protected $normalizer;

    /**
     * A resolver class, used to resolve resource keys.
     *
     * @var \Flugg\Responder\Contracts\Transformers\TransformerResolver
     */
    protected $transformerResolver;

    /**
     * A resolver class, used to resolve resource keys.
     *
     * @var \Flugg\Responder\Contracts\Resources\ResourceKeyResolver
     */
    protected $resourceKeyResolver;

    /**
     * Construct the factory class.
     *
     * @param \Flugg\Responder\Resources\DataNormalizer                   $normalizer
     * @param \Flugg\Responder\Contracts\Transformers\TransformerResolver $transformerResolver
     * @param \Flugg\Responder\Contracts\Resources\ResourceKeyResolver    $resourceKeyResolver
     */
    public function __construct(DataNormalizer $normalizer, TransformerResolver $transformerResolver, ResourceKeyResolverContract $resourceKeyResolver)
    {
        $this->normalizer = $normalizer;
        $this->transformerResolver = $transformerResolver;
        $this->resourceKeyResolver = $resourceKeyResolver;
    }

    /**
     * Make resource from the given data.
     *
     * @param  mixed                                                          $data
     * @param  \Flugg\Responder\Transformers\Transformer|string|callable|null $transformer
     * @param  string|null                                                    $resourceKey
     * @return \League\Fractal\Resource\ResourceInterface
     */
    public function make($data = null, $transformer = null, string $resourceKey = null): ResourceInterface
    {
        if ($data instanceof ResourceInterface) {
            return $this->makeFromResource($data, $transformer, $resourceKey);
        } elseif (is_null($data = $this->normalizer->normalize($data))) {
            return $this->instatiateResource($data, null, $resourceKey);
        }

        $transformer = $this->resolveTransformer($data, $transformer);
        $resourceKey = $this->resolveResourceKey($data, $resourceKey);

        return $this->instatiateResource($data, $transformer, $resourceKey);
    }

    /**
     * Make resource from the given resource.
     *
     * @param  \League\Fractal\Resource\ResourceInterface                     $resource
     * @param  \Flugg\Responder\Transformers\Transformer|string|callable|null $transformer
     * @param  string|null                                                    $resourceKey
     * @return \League\Fractal\Resource\ResourceInterface
     */
    public function makeFromResource(ResourceInterface $resource, $transformer = null, string $resourceKey = null): ResourceInterface
    {
        $transformer = $this->resolveTransformer($resource->getData(), $transformer ?: $resource->getTransformer());
        $resourceKey = $this->resolveResourceKey($resource->getData(), $resourceKey ?: $resource->getResourceKey());

        return $resource->setTransformer($transformer)->setResourceKey($resourceKey);
    }

    /**
     * Instatiate a new resource instance.
     *
     * @param  mixed                                                   $data
     * @param  \Flugg\Responder\Transformers\Transformer|callable|null $transformer
     * @param  string|null                                             $resourceKey
     * @return \League\Fractal\Resource\ResourceInterface
     */
    protected function instatiateResource($data, $transformer = null, string $resourceKey = null): ResourceInterface
    {
        if (is_null($data)) {
            return new NullResource(null, null, $resourceKey);
        } elseif ($this->shouldCreateCollection($data)) {
            return new CollectionResource($data, $transformer, $resourceKey);
        } elseif (is_scalar($data)) {
            return new Primitive($data, $transformer, $resourceKey);
        }

        return new ItemResource($data, $transformer, $resourceKey);
    }

    /**
     * Indicates if the data belongs to a collection resource.
     *
     * @param  mixed $data
     * @return bool
     */
    protected function shouldCreateCollection($data): bool
    {
        if (is_array($data)) {
            return ! Arr::isAssoc($data) && ! is_scalar(Arr::first($data));
        }

        return $data instanceof Traversable;
    }

    /**
     * Resolve a transformer.
     *
     * @param  mixed                                                          $data
     * @param  \Flugg\Responder\Transformers\Transformer|string|callable|null $transformer
     * @return \Flugg\Responder\Transformers\Transformer|callable
     */
    protected function resolveTransformer($data, $transformer)
    {
        if (isset($transformer)) {
            return $this->transformerResolver->resolve($transformer);
        }

        return $this->transformerResolver->resolveFromData($data);
    }

    /**
     * Resolve a resource key.
     *
     * @param  mixed       $data
     * @param  string|null $resourceKey
     * @return null|string
     */
    protected function resolveResourceKey($data, string $resourceKey = null)
    {
        return ! empty($resourceKey) ? $resourceKey : $this->resourceKeyResolver->resolve($data);
    }
}