<?php

namespace Flugg\Responder\Tests\Unit\Http\Factories;

use Flugg\Responder\Http\Factories\LumenResponseFactory;
use Flugg\Responder\Tests\UnitTestCase;
use Illuminate\Http\JsonResponse;
use Laravel\Lumen\Http\ResponseFactory;

/**
 * Unit tests for the [Flugg\Responder\Http\Factories\LumenResponseFactory] class.
 *
 * @see \Flugg\Responder\Http\Factories\LumenResponseFactory
 */
class LumenResponseFactoryTest extends UnitTestCase
{
    /**
     * Mock of Lumen's response factory.
     *
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    protected $baseResponseFactory;

    /**
     * Class being tested.
     *
     * @var \Laravel\Lumen\Http\ResponseFactory
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
        $this->responseFactory = new LumenResponseFactory($this->baseResponseFactory->reveal());
    }

    /**
     * Assert that [make] creates JSON responses using Lumen's response factory.
     */
    public function testMakeMethodCreatesJsonResponses()
    {
        $response = new JsonResponse($data = ['foo' => 1], $status = 201, $headers = ['x-foo' => 1]);
        $this->baseResponseFactory->json($data, $status, $headers)->willReturn($response);

        $result = $this->responseFactory->make($data, $status, $headers);

        $this->assertSame($response, $result);
    }
}
