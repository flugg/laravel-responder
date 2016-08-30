<?php

namespace Flugg\Responder\Tests\Unit;

use Flugg\Responder\Http\SuccessResponseBuilder;
use Flugg\Responder\Responder;
use Flugg\Responder\Tests\TestCase;
use Illuminate\Http\JsonResponse;

/**
 * Collection of unit tests testing [\Flugg\Responder\Responder].
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class ResponderTest extends TestCase
{
    /**
     * Test that you can resolve an instance of [\Flugg\Responder\Responder] from the service
     * container.
     *
     * @test
     */
    public function youCanResolveResponderFromTheContainer()
    {
        // Act...
        $manager = $this->app->make('responder');

        // Assert...
        $this->assertInstanceOf(Responder::class, $manager);
    }

    /**
     *
     *
     * @test
     */
    public function successMethodShouldReturnAJsonResponse()
    {
        // Arrange...
        $responder = $this->app->make('responder');

        // Act...
        $response = $responder->success();

        // Assert...
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     *
     *
     * @test
     */
    public function successMethodShouldCallOnTheSuccessResponseBuilder()
    {
        // Arrange...
        $responseBuilder = $this->mockSuccessBuilder();
        $responder = $this->app->make('responder');
        $data = $this->makeModel();
        $meta = ['foo' => true];

        // Act...
        $responder->success($data, 201, $meta);

        // Assert...
        $responseBuilder->shouldHaveReceived('transform')->with($data)->once();
        $responseBuilder->shouldHaveReceived('addMeta')->with($meta)->once();
        $responseBuilder->shouldHaveReceived('respond')->with(201)->once();
    }

    /**
     *
     *
     * @test
     */
    public function successMethodShouldAllowSkippingStatusCodeParameter()
    {
        // Arrange...
        $responseBuilder = $this->mockSuccessBuilder();
        $responder = $this->app->make('responder');
        $data = $this->makeModel();
        $meta = ['foo' => true];

        // Act...
        $responder->success($data, $meta);

        // Assert...
        $responseBuilder->shouldHaveReceived('transform')->with($data)->once();
        $responseBuilder->shouldHaveReceived('addMeta')->with($meta)->once();
    }

    /**
     *
     *
     * @test
     */
    public function successMethodShouldAllowSkippingDataParameter()
    {
        // Arrange...
        $responseBuilder = $this->mockSuccessBuilder();
        $responder = $this->app->make('responder');
        $meta = ['foo' => true];

        // Act...
        $responder->success(201, $meta);

        // Assert...
        $responseBuilder->shouldHaveReceived('addMeta')->with($meta)->once();
        $responseBuilder->shouldHaveReceived('respond')->with(201)->once();
    }

    /**
     *
     *
     * @test
     */
    public function transformMethodShouldReturnASuccessResponseBuilder()
    {
        // Arrange...
        $responder = $this->app->make('responder');

        // Act...
        $response = $responder->transform();

        // Assert...
        $this->assertInstanceOf(SuccessResponseBuilder::class, $response);
    }

    /**
     *
     *
     * @test
     */
    public function transformMethodShouldCallOnTheSuccessResponseBuilder()
    {
        // Arrange...
        $responseBuilder = $this->mockSuccessBuilder();
        $responder = $this->app->make('responder');
        $data = $this->makeModel();
        $transformer = function () { };

        // Act...
        $responder->transform($data, $transformer);

        // Assert...
        $responseBuilder->shouldHaveReceived('transform')->with($data, $transformer)->once();
    }
}