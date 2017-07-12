<?php

namespace Flugg\Responder\Transformers;

use Flugg\Responder\Contracts\Transformable;
use Flugg\Responder\Exceptions\InvalidTransformerException;
use Traversable;

/**
 * This class is responsible for resolving a transformer from a data set.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class TransformerResolver
{
    /**
     * Transformable to transformer mappings.
     *
     * @var array
     */
    protected $transformables = [];

    /**
     * Register a transformable to transformer mapping.
     *
     * @param string          $transformable
     * @param string|callback $transformer
     * @return void
     */
    public function transformable(string $transformable, $transformer)
    {
        $this->transformables = array_merge($this->transformables, [$transformable => $transformer]);
    }

    /**
     * Register multiple transformable to transformer mappings.
     *
     * @param  array $transformables
     * @return void
     */
    public function transformables(array $transformables)
    {
        foreach ($transformables as $transformable => $transformer) {
            $this->transformable($transformables);
        }
    }

    /**
     * Resolve a transformer from a transformable.
     *
     * @param  \Flugg\Responder\Contracts\Transformable $transformable
     * @return callable|\Flugg\Responder\Transformers\Transformer
     */
    public function resolve(Transformable $transformable)
    {
        if (is_null($transformer)) {
            $transformable = $this->resolveTransformable($data);

            if (! $transformer = $this->resolveTransformer($transformable)) {
                return;
            }
        }

        return $this->parse($transformer);
    }

    /**
     * Resolve a transformer class instance.
     *
     * @param  string $transformer
     * @return callable|\Flugg\Responder\Transformers\Transformer|null
     */
    public function resolveTransformer(string $transformer)
    {
        return $this->container->make($transformer);
    }

    /**
     * Resolve a transformable from the transformation data.
     *
     * @param  mixed $data
     * @return \Flugg\Responder\Contracts\Transformable|null
     */
    public function resolveTransformable($data)
    {
        if ($data instanceof Traversable && count($data)) {
            $data = array_values($data)[0];
        }

        return $data instanceof Transformable ? $data : null;
    }

    /**
     * Register model to transformer mappings.
     *
     * @param  \Flugg\Responder\Transformers\Transformer|string|callable $transformer
     * @return \Flugg\Responder\Transformers\Transformer|callable
     * @throws \Flugg\Responder\Exceptions\InvalidTransformerException
     */
    protected function parse($transformer)
    {

        if (is_callable($transformer) || $transformer instanceof Transformer) {
            return $transformer;
        }

        throw new InvalidTransformerException;
    }
}