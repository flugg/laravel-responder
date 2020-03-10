<?php

namespace Flugg\Responder\Http\Factories;

use Flugg\Responder\Contracts\Http\ResponseFactory;
use Illuminate\Contracts\Routing\ResponseFactory as BaseLaravelResponseFactory;
use Illuminate\Http\JsonResponse;

/**
 * A factory class for creating JSON responses utilizing Laravel.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class LaravelResponseFactory implements ResponseFactory
{
    /**
     * The Laravel factory for making responses.
     *
     * @var BaseLaravelResponseFactory
     */
    protected $factory;

    /**
     * Create a new Laravel response factory instance.
     *
     * @param BaseLaravelResponseFactory $factory
     */
    public function __construct(BaseLaravelResponseFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Generates a JSON response.
     *
     * @param array $data
     * @param int $status
     * @param array $headers
     * @return JsonResponse
     */
    public function make(array $data, int $status, array $headers = []): JsonResponse
    {
        return $this->factory->json($data, $status, $headers);
    }
}
