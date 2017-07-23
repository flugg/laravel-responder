<?php

namespace Flugg\Responder\Transformers;

use Flugg\Responder\Contracts\Transformable;
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
class TransformerResolver
{
    /**
     * An IoC container, used to resolve transformers.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * A registry singleton class, used to store transformer bindings.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $registry;

    /**
     * Transformable to transformer mappings.
     *
     * @var array
     */
    protected $bindings = [];

    /**
     * Construct the resolver class.
     *
     * @param \Illuminate\Contracts\Container\Container         $container
     * @param \Flugg\Responder\Transformers\TransformerRegistry $registry
     */
    public function __construct(Container $container, TransformerRegistry $registry)
    {
        $this->container = $container;
        $this->registry = $registry;
    }

    /**
     * Register a transformable to transformer mapping.
     *
     * @param  array|string    $transformable
     * @param  string|callback $transformer
     * @return void
     */
    public function bind($transformable, $transformer)
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
     * @return \Flugg\Responder\Transformers\Transformer|callable|null
     */
    public function resolveFromData($data)
    {
        if ($transformable = $this->resolveTransformable($data)) {
            if ($transformer = $this->resolveTransformer()) {
                return $this->resolve($transformer);
            }
        }

        return $this->makeClosureTransformer();
    }

    /**
     * Resolve a transformable from the transformation data.
     *
     * @param  mixed $data
     * @return \Flugg\Responder\Contracts\Transformable|null
     */
    protected function resolveTransformable($data)
    {
        if ($data instanceof Traversable && count($data)) {
            foreach ($data as $item) {
                if ($item instanceof Transformable) {
                    return $item;
                }
            }
        }

        return $data instanceof Transformable ? $data : null;
    }

    /**
     * Resolve a transformer from the transformable.
     *
     * @param  \Flugg\Responder\Contracts\Transformable $transformable
     * @return \Flugg\Responder\Contracts\Transformable|null
     */
    protected function resolveTransformer(Transformable $transformable)
    {
        if (key_exists($this->bindings, get_class($transformable))) {
            return $this->bindings[get_class($transformable)];
        }

        return $transformable->transformer();
    }

    /**
     * Make a simple closure transformer.
     *
     * @return callable
     */
    protected function makeClosureTransformer(): callable
    {
        return function ($data) {
            return $data instanceof Arrayable ? $data->toArray() : $data;
        };
    }
}