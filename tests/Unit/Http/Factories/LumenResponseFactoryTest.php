<?php

namespace Flugg\Responder\Tests\Unit\Http\Factories;

use Flugg\Responder\Http\Factories\LumenResponseFactory;
use Flugg\Responder\Tests\UnitTestCase;
use Illuminate\Http\JsonResponse;
use Laravel\Lumen\Http\ResponseFactory;
use Mockery\MockInterface;

/**
 * Unit tests for the [Flugg\Responder\Http\Responses\Factories\LumenResponseFactory] class.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class LumenResponseFactoryTest extends UnitTestCase
{
    /**
     * Mock of Lumen's response factory.
     *
     * @var MockInterface|ResponseFactory
     */
    protected $baseResponseFactory;

    /**
     * Factory class being tested.
     *
     * @var LumenResponseFactory
     */
    protected $responseFactory;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->baseResponseFactory = mock(ResponseFactory::class);
        $this->baseResponseFactory->allows('json')->andReturnUsing(function ($data, $status, $headers) {
            return new JsonResponse($data, $status, $headers);
        });

        $this->responseFactory = new LumenResponseFactory($this->baseResponseFactory);
    }

    /**
     * Assert that the [make] method creates JSON responses using Lumen's response factory.
     */
    public function testMakeMethodShouldCreateJsonResponses()
    {
        $response = $this->responseFactory->make($data = ['foo' => 1], $status = 201, $headers = ['x-foo' => 1]);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals($data, $response->getData(true));
        $this->assertEquals($status, $response->getStatusCode());
        $this->assertEquals($headers['x-foo'], $response->headers->get('x-foo'));
    }
}
