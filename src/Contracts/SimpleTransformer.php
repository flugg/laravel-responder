<?php

namespace Flugg\Responder\Contracts;

use Flugg\Responder\TransformBuilder;

/**
 * A contract for transforming data, without the serializing.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
interface SimpleTransformer
{
    /**
     * Transform the data without serializing, using the given transformer.
     *
     * @param  mixed                                                          $data
     * @param  \Flugg\Responder\Transformers\Transformer|callable|string|null $transformer
     * @param  string|null                                                    $resourceKey
     * @return \Flugg\Responder\TransformBuilder
     */
    public function make($data = null, $transformer = null, string $resourceKey = null): TransformBuilder;
}