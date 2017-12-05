<?php

namespace Flugg\Responder\Contracts;

/**
 * A contract for transforming data without serializing.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
interface Transformer
{
    /**
     * Transform the data without serializing with the given transformer and relations.
     *
     * @param  mixed                                                          $data
     * @param  \Flugg\Responder\Transformers\Transformer|callable|string|null $transformer
     * @param  string[]                                                       $with
     * @param  string[]                                                       $without
     * @return array|null
     */
    public function transform($data = null, $transformer = null, array $with = [], array $without = []): ?array;
}