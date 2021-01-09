<?php

namespace Flugg\Responder\Tests\Unit\Http;

use Flugg\Responder\Contracts\Validation\Validator;
use Flugg\Responder\Exceptions\InvalidStatusCodeException;
use Flugg\Responder\Http\ErrorResponse;
use Flugg\Responder\Tests\UnitTestCase;

/**
 * Unit tests for the [ErrorResponse] class.
 *
 * @see \Flugg\Responder\Http\ErrorResponse
 */
class ErrorResponseTest extends UnitTestCase
{
    /**
     * Class being tested.
     *
     * @var \Flugg\Responder\Http\ErrorResponse
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
     * Assert that [setCode] and [code] sets and gets error code respectively.
     */
    public function testSetAndGetCode()
    {
        $result = $this->response->setCode($code = 'error_occured');

        $this->assertSame($this->response, $result);
        $this->assertSame($code, $this->response->code());
    }

    /**
     * Assert that [setMessage] and [message] sets and gets error message respectively.
     */
    public function testSetAndGetMessage()
    {
        $result = $this->response->setMessage($message = 'An error has occured.');

        $this->assertSame($this->response, $result);
        $this->assertSame($message, $this->response->message());
    }

    /**
     * Assert that [setValidator] and [validator] sets and gets validator respectively.
     */
    public function testSetAndGetValidator()
    {
        $validator = $this->prophesize(Validator::class);

        $this->response->setValidator($validator->reveal());

        $this->assertSame($validator->reveal(), $this->response->validator());
    }

    /**
     * Assert that [setStatus] and [status] sets and gets status codes respectively.
     */
    public function testSetAndGetStatusCode()
    {
        $this->response->setStatus($status = 400);

        $this->assertSame($status, $this->response->status());
    }

    /**
     * Assert that [setStatus] throws an exception when given a success status code.
     */
    public function testSetStatusThrowsExceptionForInvalidStatusCodes()
    {
        $this->expectException(InvalidStatusCodeException::class);

        $this->response->setStatus(201);
    }

    /**
     * Assert that [setHeaders] and [headers] sets and gets status codes respectively.
     */
    public function testSetAndGetHeaders()
    {
        $this->response->setHeaders($headers = ['x-foo' => 1]);

        $this->assertSame($headers, $this->response->headers());
    }

    /**
     * Assert that [setMeta] and [meta] sets and gets metadata respectively.
     */
    public function testSetAndGetMeta()
    {
        $this->response->setMeta($meta = ['foo' => 1]);

        $this->assertSame($meta, $this->response->meta());
    }
}
