<?php

namespace Flugg\Responder\Http\Responses\Decorators;

use Illuminate\Http\JsonResponse;

/**
 * A decorator class for adding a success flag to the response data.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class SuccessFlagDecorator extends ResponseDecorator
{
    /**
     * Generate a JSON response.
     *
     * @param  array $data
     * @param  int   $status
     * @param  array $headers
     * @return \Illuminate\Http\JsonResponse
     */
    public function make(array $data, int $status, array $headers = []): JsonResponse
    {
        return $this->factory->make(array_merge([
            'success' => $status >= 100 && $status < 400,
        ], $data), $status, $headers);
    }
}
