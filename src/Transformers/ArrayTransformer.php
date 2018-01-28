<?php

namespace Flugg\Responder\Transformers;

use Illuminate\Contracts\Support\Arrayable;

/**
 * A transformer class for transforming data into an array without manipulations.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class ArrayTransformer extends Transformer
{
    /**
     * Transform the data.
     *
     * @param  mixed $data
     * @return array
     */
    public function transform($data)
    {
        return $data instanceof Arrayable ? $data->toArray() : $data;
    }
}