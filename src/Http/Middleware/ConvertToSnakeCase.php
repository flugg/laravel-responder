<?php

namespace Flugg\Responder\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TransformsRequest;

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
     * @return array
     */
    protected function cleanArray(array $data)
    {
        $parameters = [];

        foreach ($data as $key => $value) {
            $parameters[in_array($key, $this->except) ? $key : snake_case($key)] = $value;
        }

        return $parameters;
    }
}
