<?php

namespace Flugg\Responder\Http\Factories;

use Flugg\Responder\Contracts\Http\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Laravel\Lumen\Http\ResponseFactory as BaseLumenResponseFactory;

/**
 * Factory class for creating JSON responses using Lumen.
 */
class LumenResponseFactory implements ResponseFactory
{
    /**
     * Lumen factory class for making responses.
     *
     * @var \Laravel\Lumen\Http\ResponseFactory
     */
    protected $factory;

    /**
     * Create a new response factory instance.
     *
     * @param \Laravel\Lumen\Http\ResponseFactory $factory
     */
    public function __construct(BaseLumenResponseFactory $factory)
    {
        $this->factory = $factory;
    }

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
        return $this->factory->json($data, $status, $headers);
    }
}
