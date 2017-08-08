<?php

namespace Flugg\Responder\Serializers;

use Flugg\Responder\Contracts\ErrorSerializer as ErrorSerializerContract;

/**
 * A serializer class responsible for formatting error data.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class ErrorSerializer implements ErrorSerializerContract
{
    /**
     * Format the error data.
     *
     * @param  string|null $errorCode
     * @param  string|null $message
     * @param  array|null  $data
     * @return array
     */
    public function format(string $errorCode = null, string $message = null, array $data = null): array
    {
        return [
            'error' => [
                'code' => $errorCode,
                'message' => $message,
                'data' => $data,
            ],
        ];
    }
}
