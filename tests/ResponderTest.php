<?php

namespace Flugg\Responder\Tests\Unit;

use Exception;
use Flugg\Responder\Http\SuccessResponseBuilder;
use Flugg\Responder\Tests\TestCase;
use Illuminate\Http\JsonResponse;

/**
 * Collection of unit tests testing [\Flugg\Responder\Responder].
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 *
 * @covers  \Flugg\Responder\Responder::__construct
 */
class ResponderTest extends TestCase
{
    /**
     * Mock of the error response builder instance injected into [\Flugg\Responder\Responder].
     *
     * @var \Flugg\Responder\Http\ErrorResponseBuilder
     */
    protected $errorResponseBuilder;

    /**
     * Mock of the success response builder instance injected into [\Flugg\Responder\Responder].
     *
     * @var \Flugg\Responder\Http\SuccessResponseBuilder
     */
    protected $successResponseBuilder;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->successResponseBuilder = $this->mockSuccessResponseBuilder();
        $this->errorResponseBuilder = $this->mockErrorResponseBuilder();
    }

    /**
     * Test that the [error] method returns an instance of [\lluminate\Http\JsonResponse].
     *
     * @test
     * @covers \Flugg\Responder\Responder::error
     */
    public function errorMethodShouldReturnAJsonResponse()
    {
        // Arrange...
        $responder = $this->app->make('responder');

        // Act...
        $response = $responder->error();

        // Assert...
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /**
     * Test that the [error] method uses the error response builder behind the scenes.
     *
     * @test
     * @covers \Flugg\Responder\Responder::error
     */
    public function errorMethodShouldCallOnTheErrorResponseBuilder()
    {
        // Arrange...
        $responder = $this->app->make('responder');
        $code = 'error_occurred';
        $message = 'An error has occurred';

        // Act...
        $responder->error($code, 400, $message);

        // Assert...
        $this->errorResponseBuilder->shouldHaveReceived('setError')->with($code, $message)->once();
        $this->errorResponseBuilder->shouldHaveReceived('respond')->with(400)->once();
    }

    /**
     * Test that the [error] method throws an exception if the error code matches one set
     * in the configurations.
     *
     * @test
     * @covers \Flugg\Responder\Responder::error
     */
    public function errorMethodShouldThrowExceptionIfSetInConfig()
    {
        // Arrange...
        $this->expectException(Exception::class);
        $this->app->make('config')->set('responder.exceptions', ['foo' => Exception::class]);
        $responder = $this->app->make('responder');

        // Act...
        $responder->error('foo');
    }

    /**
     * Test that the [success] method returns an instance of [\lluminate\Http\JsonResponse].
     *
     * @test
     * @covers \Flugg\Responder\Responder::success
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
     * Test that the [success] method uses the success response builder behind the scenes.
     *
     * @test
     * @covers \Flugg\Responder\Responder::success
     */
    public function successMethodShouldCallOnTheSuccessResponseBuilder()
    {
        // Arrange...
        $responder = $this->app->make('responder');
        $data = $this->makeModel();
        $meta = ['foo' => true];

        // Act...
        $responder->success($data, 201, $meta);

        // Assert...
        $this->successResponseBuilder->shouldHaveReceived('transform')->with($data)->once();
        $this->successResponseBuilder->shouldHaveReceived('addMeta')->with($meta)->once();
        $this->successResponseBuilder->shouldHaveReceived('respond')->with(201)->once();
    }

    /**
     * Test that the [success] method allows skipping the second parameter.
     *
     * @test
     * @covers \Flugg\Responder\Responder::success
     */
    public function successMethodShouldAllowSkippingStatusCodeParameter()
    {
        // Arrange...
        $responder = $this->app->make('responder');
        $data = $this->makeModel();
        $meta = ['foo' => true];

        // Act...
        $responder->success($data, $meta);

        // Assert...
        $this->successResponseBuilder->shouldHaveReceived('transform')->with($data)->once();
        $this->successResponseBuilder->shouldHaveReceived('addMeta')->with($meta)->once();
        $this->successResponseBuilder->shouldHaveReceived('respond')->with(200)->once();
    }

    /**
     * Test that the [success] method allows skipping the first parameter.
     *
     * @test
     * @covers \Flugg\Responder\Responder::success
     */
    public function successMethodShouldAllowSkippingDataParameter()
    {
        // Arrange...
        $responder = $this->app->make('responder');
        $meta = ['foo' => true];

        // Act...
        $responder->success(201, $meta);

        // Assert...
        $this->successResponseBuilder->shouldHaveReceived('transform')->with(null)->once();
        $this->successResponseBuilder->shouldHaveReceived('addMeta')->with($meta)->once();
        $this->successResponseBuilder->shouldHaveReceived('respond')->with(201)->once();
    }

    /**
     * Test that the [transform] method returns an instance of [\Flugg\Responder\Http
     * \SuccessResponseBuilder].
     *
     * @test
     * @covers \Flugg\Responder\Responder::transform
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
     * Test that the [transform] method uses the success response builder.
     *
     * @test
     * @covers \Flugg\Responder\Responder::transform
     */
    public function transformMethodShouldCallOnTheSuccessResponseBuilder()
    {
        // Arrange...
        $responder = $this->app->make('responder');
        $data = $this->makeModel();
        $transformer = function () { };

        // Act...
        $responder->transform($data, $transformer);

        // Assert...
        $this->successResponseBuilder->shouldHaveReceived('transform')->with($data, $transformer)->once();
    }
}