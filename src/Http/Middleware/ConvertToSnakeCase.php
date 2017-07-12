<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TransformsRequest;

/**
 * A middleware class responsible for converting parameter keys to snake case.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 *
 * @see \Flugg\Responder\Responder
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
        return collect($data)->mapWithKeys(function ($value, $key) {
            $key = in_array($key, $this->except) ? $key : snake_case($key);

            return [$key => $value];
        })->all();
    }
}
