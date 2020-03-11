<?php

namespace Flugg\Responder\Tests\Unit\Http;

use Flugg\Responder\Exceptions\InvalidStatusCodeException;
use Flugg\Responder\Http\ErrorResponse;
use Flugg\Responder\Tests\UnitTestCase;

/**
 * Unit tests for the [Flugg\Responder\Http\ErrorResponse] class.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class ErrorResponseTest extends UnitTestCase
{
    /**
     * Class being tested.
     *
     * @var ErrorResponse
     */
    protected $response;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->response = new ErrorResponse;
    }

    /**
     * Assert that the [setErrorCode] and [errorCode] methods sets and gets error code respectively.
     */
    public function testSetAndGetErrorCode()
    {
        $result = $this->response->setErrorCode($code = 'error_occured');

        $this->assertSame($this->response, $result);
        $this->assertEquals($code, $this->response->errorCode());
    }

    /**
     * Assert that the [setMessage] and [message] methods sets and gets error message respectively.
     */
    public function testSetAndGetMessage()
    {
        $result = $this->response->setMessage($message = 'An error has occured.');

        $this->assertSame($this->response, $result);
        $this->assertEquals($message, $this->response->message());
    }

    /**
     * Assert that the [setStatus] and [status] methods sets and gets status codes respectively.
     */
    public function testSetAndGetStatusCode()
    {
        $this->response->setStatus($status = 400);

        $this->assertEquals($status, $this->response->status());
    }

    /**
     * Assert that the [setStatus] method throws an exception when given a non-successful status code.
     */
    public function testSetStatusThrowsExceptionForInvalidStatusCodes()
    {
        $this->expectException(InvalidStatusCodeException::class);

        $this->response->setStatus(201);
    }

    /**
     * Assert that the [setHeaders] and [headers] methods sets and gets status codes respectively.
     */
    public function testSetAndGetHeaders()
    {
        $this->response->setHeaders($headers = ['x-foo' => 123]);

        $this->assertEquals($headers, $this->response->headers());
    }
}
