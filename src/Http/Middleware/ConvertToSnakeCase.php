<?php

namespace Flugg\Responder\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TransformsRequest;
use Illuminate\Support\Str;

/**
 * A middleware class responsible for converting incoming parameter keys to snake case.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class ConvertToSnakeCase extends TransformsRequest
{
    /**
     * A list of attributes that shouldn't be converted.
     *
     * @var array
     */
    protected $except = [
        //
    ];

    /**
     * Clean the data in the given array.
     *
     * @param  array $data
     * @param  string $keyPrefix
     * @return array
     */
    protected function cleanArray(array $data, $keyPrefix = '')
    {
        $parameters = [];

        foreach ($data as $key => $value) {
            $parameters[in_array($keyPrefix.$key, $this->except) ? $keyPrefix.$key : Str::snake($keyPrefix.$key)] = $value;
        }

        return $parameters;
    }
}
