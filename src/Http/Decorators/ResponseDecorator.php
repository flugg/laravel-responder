<?php

namespace Flugg\Responder\Http\Decorators;

use Flugg\Responder\Contracts\Http\ResponseFactory;
use Illuminate\Http\JsonResponse;

/**
 * A decorator class for decorating responses.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
abstract class ResponseDecorator implements ResponseFactory
{
    /**
     * Factory class being decorated.
     *
     * @var ResponseFactory
     */
    protected $factory;

    /**
     * Create a new response decorator instance.
     *
     * @param ResponseFactory $factory
     */
    public function __construct(ResponseFactory $factory)
    {
        $this->factory = $factory;
    }

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
        return $this->factory->make($data, $status, $headers);
    }
}
