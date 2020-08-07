<?php

namespace Flugg\Responder\Http\Decorators;

use Flugg\Responder\Contracts\Http\ResponseFactory;
use Illuminate\Http\JsonResponse;

/**
 * Abstract decorator class for decorating responses.
 */
abstract class ResponseDecorator implements ResponseFactory
{
    /**
     * Factory class being decorated.
     *
     * @var \Flugg\Responder\Contracts\Http\ResponseFactory
     */
    protected $factory;

    /**
     * Create a new response decorator instance.
     *
     * @param \Flugg\Responder\Contracts\Http\ResponseFactory $factory
     */
    public function __construct(ResponseFactory $factory)
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
        return $this->factory->make($data, $status, $headers);
    }
}
