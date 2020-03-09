<?php

namespace Flugg\Responder\Http\Factories;

use Flugg\Responder\Contracts\Http\Factories\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Laravel\Lumen\Http\ResponseFactory as BaseLumenResponseFactory;

/**
 * A factory class for creating JSON responses utilizing Lumen.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class LumenResponseFactory implements ResponseFactory
{
    /**
     * The Lumen factory for making responses.
     *
     * @var BaseLumenResponseFactory
     */
    protected $factory;

    /**
     * Constructs the class.
     *
     * @param BaseLumenResponseFactory $factory
     */
    public function __construct(BaseLumenResponseFactory $factory)
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
