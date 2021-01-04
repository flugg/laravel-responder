<?php

namespace Flugg\Responder\Contracts\Http;

use Illuminate\Http\JsonResponse;

/**
 * Contract for a JSON response factory.
 */
interface ResponseFactory
{
    /**
     * Create a JSON response.
     *
     * @param array $data
     * @param int $status
     * @param array $headers
     * @return \Illuminate\Http\JsonResponse
     */
    public function make(array $data, int $status, array $headers = []): JsonResponse;
}
