<?php

namespace Flugg\Responder\Tests\Unit\Http\Responses;

use Flugg\Responder\Http\Responses\Factories\LumenResponseFactory;
use Flugg\Responder\Tests\TestCase;
use Illuminate\Http\JsonResponse;
use Laravel\Lumen\Http\ResponseFactory;
use Mockery;

/**
 * Unit tests for the [Flugg\Responder\Http\Responses\Factories-
 * \LumenResponseFactory] class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class LumenResponseFactoryTest extends TestCase
{
    /**
     * The base Lumen response factory mock.
     *
     * @var \Mockery\MockInterface
     */
    protected $baseResponseFactory;

    /**
     * The response factory adapter interface.
     *
     * @var \Flugg\Responder\Http\Responses\Factories\LaravelResponseFactory
     */
    protected $responseFactory;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->baseResponseFactory = Mockery::mock(ResponseFactory::class);
        $this->baseResponseFactory->shouldReceive('json')->andReturnUsing(function ($data, $status, $headers) {
            return new JsonResponse($data, $status, $headers);
        });

        $this->responseFactory = new LumenResponseFactory($this->baseResponseFactory);
    }

    /**
     * Test that the [make] method decorates the response data with info about status code.
     */
    public function testMakeMethodShouldCreateJsonResponse()
    {
        $data    = ['foo' => 1]; 
        $status  = 201;
        $headers = ['x-foo' => 1];
        $response = $this->responseFactory->make($data, $status, $headers);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals($data, $response->getData(true));
        $this->assertEquals($status, $response->getStatusCode());
        $this->assertEquals($headers['x-foo'], $response->headers->get('x-foo'));
    }
}