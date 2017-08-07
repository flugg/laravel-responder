<?php

namespace Flugg\Responder\Http\Responses\Decorators;

use Flugg\Responder\Contracts\ResponseFactory;
use Illuminate\Http\JsonResponse;

/**
 * A decorator class for decorating responses.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
abstract class ResponseDecorator implements ResponseFactory
{
    /**
     * The factory being decorated.
     *
     * @var \Flugg\Responder\Contracts\ResponseFactory
     */
    protected $factory;

    /**
     * Construct the decorator class.
     *
     * @param \Flugg\Responder\Contracts\ResponseFactory $factory
     */
    public function __construct(ResponseFactory $factory)
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
    abstract public function make(array $data, int $status, array $headers = []): JsonResponse;
}
