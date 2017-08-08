<?php

namespace Flugg\Responder\Contracts\Resources;

use League\Fractal\Resource\ResourceInterface;

/**
 * A contract for creating resources.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
interface ResourceFactory
{
    /**
     * Make resource from the given data.
     *
     * @param  mixed                                                          $data
     * @param  \Flugg\Responder\Transformers\Transformer|string|callable|null $transformer
     * @param  string|null                                                    $resourceKey
     * @return \League\Fractal\Resource\ResourceInterface
     */
    public function make($data = null, $transformer = null, string $resourceKey = null): ResourceInterface;
}