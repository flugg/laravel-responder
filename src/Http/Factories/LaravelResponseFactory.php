<?php

namespace Flugg\Responder\Http\Factories;

use Flugg\Responder\Contracts\Http\ResponseFactory;
use Illuminate\Contracts\Routing\ResponseFactory as BaseLaravelResponseFactory;
use Illuminate\Http\JsonResponse;

/**
 * Factory class for creating JSON responses using Laravel.
 */
class LaravelResponseFactory implements ResponseFactory
{
    /**
     * Laravel factory class for making responses.
     *
     * @var \Illuminate\Contracts\Routing\ResponseFactory
     */
    protected $factory;

    /**
     * Create a new response factory instance.
     *
     * @param \Illuminate\Contracts\Routing\ResponseFactory $factory
     */
    public function __construct(BaseLaravelResponseFactory $factory)
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
