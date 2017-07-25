<?php

namespace Flugg\Responder\Tests\Unit\Http\Responses;

use Flugg\Responder\ErrorFactory;
use Flugg\Responder\Http\Responses\ErrorResponseBuilder;
use Flugg\Responder\Serializers\JsonSerializer;
use Flugg\Responder\Tests\TestCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Mockery;

/**
 * Unit tests for the [Flugg\Responder\Http\Responses-
 * \ErrorResponseBuilderTest] class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class ErrorResponseBuilderTest extends TestCase
{
    /**
     * The response factory mock.
     *
     * @var \Mockery\MockInterface
     */
    protected $responseFactory;

    /**
     * The error factory mock.
     *
     * @var \Mockery\MockInterface
     */
    protected $errorFactory;

    /**
     * The error response builder.
     *
     * @var \Flugg\Responder\Http\Responses\ErrorResponseBuilder
     */
    protected $responseBuilder;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->responseFactory = $this->mockResponseFactory();
        $this->errorFactory = Mockery::mock(ErrorFactory::class);
        $this->responseBuilder = new ErrorResponseBuilder($this->responseFactory, $this->errorFactory);
    }

    /**
     * Test that the [respond] method calls on the response factory to generate a JSON response.
     */
    public function testRespondMethodShouldMakeAResponseUsingTheResponseFactory()
    {
        [$status, $headers] = [400, ['x-foo' => 1]];
        $this->errorFactory->shouldReceive('make')->andReturn($error = ['foo' => 1]);
        $this->responseFactory->shouldReceive('make')
            ->andReturn($response = new JsonResponse($error, $status, $headers));

        $result = $this->responseBuilder->respond($status, $headers);

        $this->assertEquals($response, $result);
        $this->responseFactory->shouldHaveReceived('make')->with($error, $status, $headers)->once();
    }

    /**
     * Test that the [respond] method throws exception if status code is not a valid error code.
     */
    public function testRespondMethodThrowsExceptionIfStatusCodeIsInvalid()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->responseBuilder->respond($status = 200);
    }

    /**
     * Test that the [toArray] method formats the error output using the error factory and
     * returns the result as an array.
     */
    public function testToArrayMethodShouldFormatErrorWithTheErrorFactoryAndReturnArray()
    {
        $this->errorFactory->shouldReceive('make')->andReturn($error = ['foo' => 1]);

        $result = $this->responseBuilder->toArray();

        $this->assertEquals($error, $result);
    }

    /**
     * Test that the [toCollection] method formats the error output using the error factory
     * and returns the result as a collection.
     */
    public function testToCollectionMethodShouldFormatErrorWithTheErrorFactoryAndReturnCollection()
    {
        $this->errorFactory->shouldReceive('make')->andReturn($error = ['foo' => 1]);

        $result = $this->responseBuilder->toCollection();

        $this->assertEquals(new Collection($error), $result);
    }

    /**
     * Test that the [toJson] method formats the error output using the error factory and
     * returns the result as JSON.
     */
    public function testToJsonMethodShouldFormatErrorWithTheErrorFactoryAndReturnJson()
    {
        $this->errorFactory->shouldReceive('make')->andReturn($error = ['foo' => 1]);

        $result = $this->responseBuilder->toJson();

        $this->assertEquals(json_encode($error), $result);
    }

    /**
     * Test that the [toJson] method accepts an argument for setting encoding options.
     */
    public function testToJsonMethodShouldAllowSettingOptions()
    {
        $this->errorFactory->shouldReceive('make')->andReturn($error = ['foo' => 1]);

        $result = $this->responseBuilder->toJson(JSON_PRETTY_PRINT);

        $this->assertEquals(json_encode($error, JSON_PRETTY_PRINT), $result);
    }

    /**
     * Test that the [error] method sets the error code and message that is sent to the error factory.
     */
    public function testErrorMethodSetsErrorCodeAndMessage()
    {
        [$code, $message] = ['test_error', 'A test error has occured.'];
        $this->errorFactory->shouldReceive('make')->andReturn([]);

        $this->responseBuilder->error($code, $message)->toArray();

        $this->errorFactory->shouldHaveReceived('make')->with($code, $message, null)->once();
    }

    /**
     * Test that the [data] method adds error data that is sent to the error factory.
     */
    public function testDataMethodSetsErrorData()
    {
        $this->errorFactory->shouldReceive('make')->andReturn([]);

        $this->responseBuilder->data($data = ['foo' => 1])->toArray();

        $this->errorFactory->shouldHaveReceived('make')->with(null, null, $data)->once();
    }
}