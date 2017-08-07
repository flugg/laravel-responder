<?php

namespace Flugg\Responder\Http\Responses\Factories;

use Flugg\Responder\Contracts\ResponseFactory;
use Illuminate\Contracts\Routing\ResponseFactory as BaseLaravelResponseFactory;
use Illuminate\Http\JsonResponse;

/**
 * A factory class for creating JSON responses utilizing Laravel.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class LaravelResponseFactory implements ResponseFactory
{
    /**
     * The Laravel factory for making responses.
     *
     * @var \Illuminate\Contracts\Routing\ResponseFactory
     */
    protected $factory;

    /**
     * Construct the factory class.
     *
     * @param \Illuminate\Contracts\Routing\ResponseFactory $factory
     */
    public function __construct(BaseLaravelResponseFactory $factory)
    {
        $this->factory = $factory;
    }

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
        return $this->factory->json($data, $status, $headers);
    }
}