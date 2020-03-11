<?php

namespace Flugg\Responder\Http\Factories;

use Flugg\Responder\Contracts\Http\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Laravel\Lumen\Http\ResponseFactory as BaseLumenResponseFactory;

/**
 * Factory class for creating JSON responses using Lumen.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class LumenResponseFactory implements ResponseFactory
{
    /**
     * Lumen factory class for making responses.
     *
     * @var BaseLumenResponseFactory
     */
    protected $factory;

    /**
     * Create a new response factory instance.
     *
     * @param BaseLumenResponseFactory $factory
     */
    public function __construct(BaseLumenResponseFactory $factory)
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
        return $this->factory->json($data, $status, $headers);
    }
}
