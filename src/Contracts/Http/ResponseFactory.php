<?php

namespace Flugg\Responder\Contracts\Http;

use Illuminate\Http\JsonResponse;

/**
 * A contract for creating JSON responses.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
interface ResponseFactory
{
    /**
     * Generate a JSON response.
     *
     * @param array $data
     * @param int $status
     * @param array $headers
     * @return JsonResponse
     */
    public function make(array $data, int $status, array $headers = []): JsonResponse;
}
