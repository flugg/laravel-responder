<?php

namespace Flugg\Responder\Tests\Unit\Http\Factories;

use Flugg\Responder\Http\Factories\LaravelResponseFactory;
use Flugg\Responder\Tests\UnitTestCase;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;

/**
 * Unit tests for the [Flugg\Responder\Http\Factories\LaravelResponseFactory] class.
 *
 * @see \Flugg\Responder\Http\Factories\LaravelResponseFactory
 */
class LaravelResponseFactoryTest extends UnitTestCase
{
    /**
     * Mock of Laravel's response factory.
     *
     * @var \Mockery\MockInterface|\Illuminate\Contracts\Routing\ResponseFactory
     */
    protected $baseResponseFactory;

    /**
     * Class being tested.
     *
     * @var \Flugg\Responder\Http\Factories\LaravelResponseFactory
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

        $this->responseFactory = new LaravelResponseFactory($this->baseResponseFactory);
    }

    /**
     * Assert that [make] creates JSON responses using Laravel's response factory.
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
