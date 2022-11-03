<?php

namespace Flugg\Responder\Tests\Unit;

use Flugg\Responder\Tests\TestCase;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

/**
 * Collection of unit tests for the [\Flugg\Responder\Http\ErrorResponseBuilder].
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class ErrorResponseBuilderTest extends TestCase
{
    /**
     * Test that the [respond] method converts the error response into an instance of
     * [\Illuminate\Http\JsonResponse] with a default status code of 500.
     *
     * @test
     * @covers \Flugg\Responder\Http\ErrorResponseBuilder::respond
     * @covers \Flugg\Responder\Http\ResponseBuilder::respond
     * @covers \Flugg\Responder\Http\ResponseBuilder::includeStatusCode
     */
    public function respondMethodShouldReturnAJsonResponse()
    {
        // Arrange...
        $responseBuilder = $this->app->make('responder.error');

        // Act...
        $response = $responseBuilder->respond();

        // Assert...
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals($response->status(), 500);
    }

    /**
     * Test that the [respond] method allows passing a status code as the first parameter.
     *
     * @test
     * @covers \Flugg\Responder\Http\ErrorResponseBuilder::respond
     * @covers \Flugg\Responder\Http\ResponseBuilder::respond
     * @covers \Flugg\Responder\Http\ResponseBuilder::includeStatusCode
     */
    public function respondMethodShouldAllowSettingStatusCode()
    {
        // Arrange...
        $responseBuilder = $this->app->make('responder.error');

        // Act...
        $response = $responseBuilder->respond(400);

        // Assert...
        $this->assertEquals($response->status(), 400);
    }

    /**
     * Test that you can set any headers to the JSON response by passing a second argument
     * to the [respond] method.
     *
     * @test
     * @covers \Flugg\Responder\Http\ErrorResponseBuilder::respond
     * @covers \Flugg\Responder\Http\ResponseBuilder::respond
     * @covers \Flugg\Responder\Http\ResponseBuilder::includeStatusCode
     */
    public function respondMethodShouldAllowSettingHeaders()
    {
        // Arrange...
        $responseBuilder = $this->app->make('responder.error');

        // Act...
        $response = $responseBuilder->respond(400, [
            'x-foo' => true
        ]);

        // Assert...
        $this->assertArrayHasKey('x-foo', $response->headers->all());
    }

    /**
     * Test that the [setStatus] method sets the HTTP status code on the response, providing
     * an alternative, more explicit way of setting the status code.
     *
     * @test
     * @covers \Flugg\Responder\Http\ErrorResponseBuilder::setStatus
     * @covers \Flugg\Responder\Http\ResponseBuilder::setStatus
     */
    public function setStatusMethodShouldSetStatusCode()
    {
        // Arrange...
        $responseBuilder = $this->app->make('responder.error');

        // Act...
        $responseBuilder->setStatus(400);

        // Assert...
        $this->assertEquals($responseBuilder->respond()->status(), 400);
    }

    /**
     * Test that the [setStatus] method throws an [\InvalidArgumentException] when the status
     * code given is not a valid error HTTP status code.
     *
     * @test
     * @covers \Flugg\Responder\Http\ErrorResponseBuilder::setStatus
     * @covers \Flugg\Responder\Http\ResponseBuilder::setStatus
     */
    public function setStatusMethodShouldFailIfStatusCodeIsInvalid()
    {
        // Arrange...
        $responseBuilder = $this->app->make('responder.error');
        $this->expectException(InvalidArgumentException::class);

        // Act...
        $responseBuilder->setStatus(200);
    }

    /**
     * Test that the [setStatus] method returns the response builder, allowing for fluent
     * method chaining.
     *
     * @test
     * @covers \Flugg\Responder\Http\ErrorResponseBuilder::setStatus
     * @covers \Flugg\Responder\Http\ResponseBuilder::setStatus
     */
    public function setStatusMethodShouldReturnItself()
    {
        // Arrange...
        $responseBuilder = $this->app->make('responder.error');

        // Act...
        $result = $responseBuilder->setStatus(400);

        // Assert...
        $this->assertSame($responseBuilder, $result);
    }

    /**
     * Test that error data is added when an error code is set using the [setError] method.
     *
     * @test
     * @covers \Flugg\Responder\Http\ErrorResponseBuilder::setError
     */
    public function setErrorMethodShouldAddErrorData()
    {
        // Arrange...
        $responseBuilder = $this->app->make('responder.error');

        // Act...
        $responseBuilder->setError('testing_error');

        // Assert...
        $this->assertEquals([
            'success' => false,
            'error' => [
                'code' => 'testing_error',
                'message' => null
            ]
        ], $responseBuilder->toArray());
    }

    /**
     * Test that the [setError] method attempts to resolve an error message from the translator.
     *
     * @test
     * @covers \Flugg\Responder\Http\ErrorResponseBuilder::setError
     */
    public function setErrorMethodShouldResolveErrorMessageFromTranslator()
    {
        // Arrange...
        $this->mockTranslator('Testing error');
        $responseBuilder = $this->app->make('responder.error');

        // Act...
        $responseBuilder->setError('testing_error');

        // Assert...
        $this->assertEquals([
            'success' => false,
            'error' => [
                'code' => 'testing_error',
                'message' => 'Testing error'
            ]
        ], $responseBuilder->toArray());
    }

    /**
     * Test that the [setError] method should allow passing any parameters to the translator
     * when resolving the error message.
     *
     * @test
     * @covers \Flugg\Responder\Http\ErrorResponseBuilder::setError
     */
    public function setErrorMethodShouldAllowAddingParametersToMessage()
    {
        // Arrange...
        $translator = $this->mockTranslator('Testing error foo');
        $responseBuilder = $this->app->make('responder.error');
        $parameters = ['name' => 'foo'];

        // Act...
        $responseBuilder->setError('testing_error', $parameters);

        // Assert...
        $this->assertEquals([
            'success' => false,
            'error' => [
                'code' => 'testing_error',
                'message' => 'Testing error foo'
            ]
        ], $responseBuilder->toArray());
        $translator->shouldHaveReceived('trans')->with('errors.testing_error', $parameters);
    }

    /**
     * Test that the [setError] method allows passing a string as second argument instead of an
     * array of parameters, which will override the error message and set it explicitly.
     *
     * @test
     * @covers \Flugg\Responder\Http\ErrorResponseBuilder::setError
     */
    public function setErrorMethodShouldAllowOverridingErrorMessage()
    {
        // Arrange...
        $this->mockTranslator('Testing error 1');
        $responseBuilder = $this->app->make('responder.error');

        // Act...
        $responseBuilder->setError('testing_error', 'Testing error 2');

        // Assert...
        $this->assertEquals([
            'success' => false,
            'error' => [
                'code' => 'testing_error',
                'message' => 'Testing error 2'
            ]
        ], $responseBuilder->toArray());
    }

    /**
     * Test that the [toArray] method serializes the data given, using the default serializer
     * and no data.
     *
     * @test
     * @covers \Flugg\Responder\Http\ErrorResponseBuilder::toArray
     * @covers \Flugg\Responder\Http\ErrorResponseBuilder::buildErrorData
     * @covers \Flugg\Responder\Http\ErrorResponseBuilder::resolveMessage
     */
    public function toArrayMethodShouldSerializeData()
    {
        // Arrange...
        $responseBuilder = $this->app->make('responder.error');

        // Act...
        $array = $responseBuilder->setError('foo')->toArray();

        // Assert...
        $this->assertEquals([
            'success' => false,
            'error' => null
        ], $array);
    }

    /**
     * Test that the [toCollection] serializes the data into a collection.
     *
     * @test
     * @covers \Flugg\Responder\Http\ErrorResponseBuilder::toCollection
     */
    public function toCollectionMethodShouldReturnACollection()
    {
        // Arrange...
        $responseBuilder = $this->app->make('responder.error');

        // Act...
        $collection = $responseBuilder->toCollection();

        // Assert...
        $this->assertEquals(collect([
            'success' => false,
            'error' => null
        ]), $collection);
    }

    /**
     * Test that the [toJson] serializes the data into a JSON string.
     *
     * @test
     * @covers \Flugg\Responder\Http\ErrorResponseBuilder::toJson
     */
    public function toJsonMethodShouldReturnJson()
    {
        // Arrange...
        $responseBuilder = $this->app->make('responder.error');

        // Act...
        $json = $responseBuilder->toCollection();

        // Assert...
        $this->assertEquals(json_encode([
            'success' => false,
            'error' => null
        ]), $json);
    }
}