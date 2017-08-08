<?php

namespace Flugg\Responder\Transformers;

use Flugg\Responder\Contracts\Transformable;
use Flugg\Responder\Contracts\Transformers\TransformerResolver as TransformerResolverContract;
use Flugg\Responder\Exceptions\InvalidTransformerException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Support\Arrayable;
use Traversable;

/**
 * This class is responsible for resolving transformers.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class TransformerResolver implements TransformerResolverContract
{
    /**
     * A container used to resolve transformers.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * Transformable to transformer mappings.
     *
     * @var array
     */
    protected $bindings = [];

    /**
     * Construct the resolver class.
     *
     * @param \Illuminate\Contracts\Container\Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Register a transformable to transformer binding.
     *
     * @param  string|array         $transformable
     * @param  string|callback|null $transformer
     * @return void
     */
    public function bind($transformable, $transformer = null)
    {
        $this->bindings = array_merge($this->bindings, is_array($transformable) ? $transformable : [
            $transformable => $transformer,
        ]);
    }

    /**
     * Resolve a transformer.
     *
     * @param  \Flugg\Responder\Transformers\Transformer|string|callable $transformer
     * @return \Flugg\Responder\Transformers\Transformer|callable
     * @throws \Flugg\Responder\Exceptions\InvalidTransformerException
     */
    public function resolve($transformer)
    {
        if (is_string($transformer)) {
            return $this->container->make($transformer);
        }

        if (! is_callable($transformer) && ! $transformer instanceof Transformer) {
            throw new InvalidTransformerException;
        }

        return $transformer;
    }

    /**
     * Resolve a transformer from the given data.
     *
     * @param  mixed $data
     * @return \Flugg\Responder\Transformers\Transformer|callable
     */
    public function resolveFromData($data)
    {
        $transformable = $this->resolveTransformable($data);
        $transformer = $this->resolveTransformer($transformable);

        return $this->resolve($transformer);
    }

    /**
     * Resolve a transformable from the given data.
     *
     * @param  mixed $data
     * @return mixed
     */
    protected function resolveTransformable($data)
    {
        if (is_array($data) || $data instanceof Traversable) {
            foreach ($data as $item) {
                return $item;
            }
        }

        return $data;
    }

    /**
     * Resolve a transformer from the transformable element.
     *
     * @param  mixed $transformable
     * @return \Flugg\Responder\Contracts\Transformable|callable
     */
    protected function resolveTransformer($transformable)
    {
        if (is_object($transformable) && key_exists(get_class($transformable), $this->bindings)) {
            return $this->bindings[get_class($transformable)];
        }

        if ($transformable instanceof Transformable) {
            return $transformable->transformer();
        }

        return $this->resolveFallbackTransformer();
    }

    /**
     * Resolve a fallback closure transformer just returning the data directly.
     *
     * @return callable
     */
    protected function resolveFallbackTransformer()
    {
        return function ($data) {
            return $data instanceof Arrayable ? $data->toArray() : $data;
        };
    }
}