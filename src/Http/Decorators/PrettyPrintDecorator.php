<?php

namespace Flugg\Responder\Http\Decorators;

use Illuminate\Http\JsonResponse;

/**
 * Decorator class for generating JSON using the "JSON_PRETTY_PRINT" option.
 *
 * @package flugger/laravel-responder
 * @author Paolo Caleffi <p.caleffi@dreamonkey.com>
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class PrettyPrintDecorator extends ResponseDecorator
{
    /**
     * Generate a JSON response.
     *
     * @param array $data
     * @param int $status
     * @param array $headers
     * @return JsonResponse
     */
    public function make(array $data, int $status, array $headers = []): JsonResponse
    {
        return tap(parent::make($data, $status, $headers), function (JsonResponse $response) {
            $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);
        });
    }
}
