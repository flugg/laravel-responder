<?php

namespace Flugg\Responder\Contracts\Transformers;

/**
 * A contract for resolving transformers.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
interface TransformerResolver
{
    /**
     * Register a transformable to transformer binding.
     *
     * @param  string|array    $transformable
     * @param  string|callback $transformer
     * @return void
     */
    public function bind($transformable, $transformer);

    /**
     * Resolve a transformer.
     *
     * @param  \Flugg\Responder\Transformers\Transformer|string|callable $transformer
     * @return \Flugg\Responder\Transformers\Transformer|callable
     * @throws \Flugg\Responder\Exceptions\InvalidTransformerException
     */
    public function resolve($transformer);

    /**
     * Resolve a transformer from the given data.
     *
     * @param  mixed $data
     * @return \Flugg\Responder\Transformers\Transformer|callable
     */
    public function resolveFromData($data);
}