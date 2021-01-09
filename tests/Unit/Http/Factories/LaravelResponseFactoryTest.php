<?php

namespace Flugg\Responder\Tests\Unit\Http\Factories;

use Flugg\Responder\Http\Factories\LaravelResponseFactory;
use Flugg\Responder\Tests\UnitTestCase;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;

/**
 * Unit tests for the [LaravelResponseFactory] class.
 *
 * @see \Flugg\Responder\Http\Factories\LaravelResponseFactory
 */
class LaravelResponseFactoryTest extends UnitTestCase
{
    /**
     * Mock of an [\Illuminate\Contracts\Routing\ResponseFactory] class.
     *
     * @var \Prophecy\Prophecy\ObjectProphecy
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

        $this->baseResponseFactory = $this->prophesize(ResponseFactory::class);
        $this->responseFactory = new LaravelResponseFactory($this->baseResponseFactory->reveal());
    }

    /**
     * Assert that [make] creates JSON responses using Laravel's response factory.
     */
    public function testMakeMethodCreatesJsonResponses()
    {
        $response = new JsonResponse($data = ['foo' => 1], $status = 201, $headers = ['x-foo' => 1]);
        $this->baseResponseFactory->json($data, $status, $headers)->willReturn($response);

        $result = $this->responseFactory->make($data, $status, $headers);

        $this->assertSame($response, $result);
    }
}
