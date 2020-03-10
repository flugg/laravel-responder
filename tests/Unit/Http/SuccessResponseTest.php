<?php

namespace Flugg\Responder\Tests\Unit\Http;

use Flugg\Responder\Exceptions\InvalidStatusCodeException;
use Flugg\Responder\Http\SuccessResponse;
use Flugg\Responder\Tests\UnitTestCase;

/**
 * Unit tests for the [Flugg\Responder\Http\SuccessResponse] class.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class SuccessResponseTest extends UnitTestCase
{
    /**
     * The class being tested.
     *
     * @var SuccessResponse
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

        $this->response = new SuccessResponse;
    }

    /**
     * Assert that the [setData] and [data] methods sets and gets data respectively.
     */
    public function testSetAndGetData()
    {
        $result = $this->response->setData($data = ['foo' => 123]);

        $this->assertSame($this->response, $result);
        $this->assertEquals($data, $this->response->data());
    }

    /**
     * Assert that the [setStatus] and [status] methods sets and gets status codes respectively.
     */
    public function testSetAndGetStatusCode()
    {
        $this->response->setStatus($status = 201);

        $this->assertEquals($status, $this->response->status());
    }

    /**
     * Assert that the [setStatus] method throws an exception when given a non-successful status code.
     */
    public function testSetStatusThrowsExceptionForInvalidStatusCodes()
    {
        $this->expectException(InvalidStatusCodeException::class);

        $this->response->setStatus(400);
    }

    /**
     * Assert that the [setHeaders] and [headers] methods sets and gets status codes respectively.
     */
    public function testSetAndGetHeaders()
    {
        $this->response->setHeaders($headers = ['x-foo' => 123]);

        $this->assertEquals($headers, $this->response->headers());
    }

    /**
     * Assert that the [setMeta] and [meta] methods sets and gets meta data respectively.
     */
    public function testSetAndGetMeta()
    {
        $this->response->setMeta($meta = ['foo' => 123]);

        $this->assertEquals($meta, $this->response->meta());
    }
}
