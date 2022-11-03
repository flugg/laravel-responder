<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TransformsRequest;

class ConvertToSnakeCase extends TransformsRequest
{
    /**
     * The attributes that should not be trimmed.
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
            return [$this->transformKey($key) => $value];
        })->all();
    }

    /**
     * Transform the given parameter key.
     *
     * @param  string $key
     * @return mixed
     */
    protected function transformKey($key)
    {
        if (in_array($key, $this->except)) {
            return $value;
        }

        return snake_case($key);
    }
}
