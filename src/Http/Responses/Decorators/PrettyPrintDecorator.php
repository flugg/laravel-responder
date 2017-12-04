<?php

namespace Flugg\Responder\Http\Responses\Decorators;

use Illuminate\Http\JsonResponse;

/**
 * A decorator class for returning JSON data with pretty print option on.
 *
 * @package flugger/laravel-responder
 * @author  Paolo Caleffi <p.caleffi@dreamonkey.com>
 * @license The MIT License
 */
class PrettyPrintDecorator extends ResponseDecorator
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
        $response = $this->factory->make($data, $status, $headers);

        $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);

        return $response;
    }
}