<?php

namespace Flugg\Responder\Tests\Unit;

use Flugg\Responder\Exceptions\Http\HttpException;
use Flugg\Responder\Tests\TestCase;

/**
 * Unit tests for the [Flugg\Responder\Exceptions\Handler] class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
final class HttpExceptionTest extends TestCase
{
    /**
     * A stub of the package's [Handler] class.
     *
     * @var \Flugg\Responder\Exceptions\Http\HttpException
     */
    protected $exception;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->exception = new class () extends HttpException {
            protected $status = 404;
            protected $errorCode = 'test_error';
            protected $message = 'An error has occured.';
            protected $data = ['foo' => 1];
            protected $headers = ['x-foo' => true];
        };
    }

    /**
     * Assert that the [statusCode] method returns the set status code.
     */
    public function testStatusCodeMethodReturnsStatusCode(): void
    {
        $status = $this->exception->statusCode();

        $this->assertEquals(404, $status);
    }

    /**
     * Assert that the [errorCode] method returns the set error code.
     */
    public function testErrorCodeMethodReturnsErrorCode(): void
    {
        $errorCode = $this->exception->errorCode();

        $this->assertEquals('test_error', $errorCode);
    }

    /**
     * Assert that the [message] method returns the set error message.
     */
    public function testMessageMethodReturnsErrorMessage(): void
    {
        $message = $this->exception->message();

        $this->assertEquals('An error has occured.', $message);
    }

    /**
     * Assert that the [data] method returns the set error data.
     */
    public function testDataMethodReturnsErrorData(): void
    {
        $data = $this->exception->data();

        $this->assertEquals(['foo' => 1], $data);
    }

    /**
     * Assert that the [headers] method returns the attached headers.
     */
    public function testHeadersMethodReturnsHeaders(): void
    {
        $data = $this->exception->headers();

        $this->assertEquals(['x-foo' => true], $data);
    }
}
