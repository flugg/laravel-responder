<?php

namespace Flugg\Responder\Http\Decorators;

use Illuminate\Http\JsonResponse;

/**
 * Decorator class for creating JSON using the "JSON_PRETTY_PRINT" option.
 */
class PrettyPrintDecorator extends ResponseDecorator
{
    /**
     * Create a JSON response.
     *
     * @param array $data
     * @param int $status
     * @param array $headers
     * @return \Illuminate\Http\JsonResponse
     */
    public function make(array $data, int $status, array $headers = []): JsonResponse
    {
        return tap(parent::make($data, $status, $headers), function (JsonResponse $response) {
            $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);
        });
    }
}
