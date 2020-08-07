<?php

namespace Flugg\Responder\Http\Decorators;

use Illuminate\Http\JsonResponse;

/**
 * Decorator class for escaping HTML entities in string values on the response data.
 */
class EscapeHtmlDecorator extends ResponseDecorator
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
        array_walk_recursive($data, function (&$value) {
            if (is_string($value)) {
                $value = e($value);
            }
        });

        return parent::make($data, $status, $headers);
    }
}
