<?php

namespace Flugg\Responder\Tests\Unit\Facades;

use Flugg\Responder\Tests\IntegrationTestCase;
use Illuminate\Testing\TestResponse;
use Mockery\MockInterface;

/**
 * Integration tests for testing [Illuminate\Testing\TestResponse] macros.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class TestResponseMacrosTest extends IntegrationTestCase
{
    /**
     * A partial mock of a test response.
     *
     * @var MockInterface|TestResponse
     */
    protected $testResponse;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->testResponse = mock(TestResponse::class)->makePartial();
    }

    /**
     * Assert that you can call the [assertSuccess] method on a test response to assert a valid success response.
     */
    public function testMacroAssertsASuccessResponse(): void
    {
        $this->testResponse->allows('getStatusCode')->andReturn(200);
        $this->testResponse->allows('assertExactJson')->andReturn();

        $result = $this->testResponse->assertSuccess($data = ['foo' => 123]);

        $this->assertSame($this->testResponse, $result);
        $this->testResponse->shouldHaveReceived('assertExactJson')->with([
            'data' => $data,
        ]);
    }

    /**
     * Assert that you can call the [assertError] method on a test response to assert a valid error response.
     */
    public function testMacroAssertsAnErrorResponse(): void
    {
        $this->testResponse->allows('getStatusCode')->andReturn(500);
        $this->testResponse->allows('assertExactJson')->andReturn();

        $result = $this->testResponse->assertError($errorCode = 'error_occured', $message = 'An error has occured.');

        $this->assertSame($this->testResponse, $result);
        $this->testResponse->shouldHaveReceived('assertExactJson')->with([
            'error' => [
                'code' => $errorCode,
                'message' => $message,
            ],
        ]);
    }

    /**
     * Assert that you can call the [assertValidationErrors] method on a test response to assert a valid error response
     * including validation errors.
     */
    public function testMacroAssertsAValidationErrorResponse(): void
    {
        $this->testResponse->allows('getStatusCode')->andReturn(422);
        $this->testResponse->allows('assertExactJson')->andReturn();

        $result = $this->testResponse->assertValidationErrors([
            'foo' => ['min:10', 'email'],
            'bar.baz' => 'required',
        ]);

        $this->assertSame($this->testResponse, $result);
        $this->testResponse->shouldHaveReceived('assertExactJson')->with([
            'error' => [
                'code' => 'validation_failed',
                'message' => 'The given data was invalid',
                'fields' => [
                    'foo' => [
                        ['rule' => 'min', 'message' => 'The foo must be at least 10 characters.'],
                        ['rule' => 'email', 'message' => 'The foo must be a valid email address.'],
                    ],
                    'bar.baz' => [
                        ['rule' => 'required', 'message' => 'The bar.baz field is required.'],
                    ],
                ],
            ],
        ]);
    }
}
