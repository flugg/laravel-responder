<?php

namespace Flugg\Responder\Tests\Integration;

use Flugg\Responder\Tests\IntegrationTestCase;
use Illuminate\Testing\TestResponse;
use Mockery;

/**
 * Integration tests for testing macros.
 */
class TestResponseMacrosTest extends IntegrationTestCase
{
    /**
     * Partial mock of a test response.
     *
     * @var \Illuminate\Testing\TestResponse|\Mockery\MockInterface
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

        $this->testResponse = Mockery::mock(TestResponse::class)->makePartial();
    }

    /**
     * Assert that [assertSuccess] asserts for a a valid success response.
     */
    public function testMacroAssertsForValidSuccessResponse(): void
    {
        $this->testResponse->allows('getStatusCode')->andReturn(200);
        $this->testResponse->allows('assertExactJson')->andReturn();

        $result = $this->testResponse->assertSuccess($data = ['foo' => 1]);

        $this->assertSame($this->testResponse, $result);
        $this->testResponse->shouldHaveReceived('assertExactJson')->with([
            'data' => $data,
        ])->once();
    }

    /**
     * Assert that [assertError] asserts for a a valid error response.
     */
    public function testMacroAssertsForValidErrorResponse(): void
    {
        $this->testResponse->allows('getStatusCode')->andReturn(500);
        $this->testResponse->allows('assertExactJson')->andReturn();

        $result = $this->testResponse->assertError($code = 'error_occured', $message = 'An error has occured.');

        $this->assertSame($this->testResponse, $result);
        $this->testResponse->shouldHaveReceived('assertExactJson')->with([
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ])->once();
    }

    /**
     * Assert that [assertValidationErrors] asserts for a a valid error response with validation errors.
     */
    public function testMacroAssertsForValidValidationErrorResponse(): void
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
        ])->once();
    }
}
