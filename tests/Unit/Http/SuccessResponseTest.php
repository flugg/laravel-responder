<?php

namespace Flugg\Responder\Tests\Unit\Http;

use Flugg\Responder\Contracts\Pagination\CursorPaginator;
use Flugg\Responder\Contracts\Pagination\Paginator;
use Flugg\Responder\Exceptions\InvalidStatusCodeException;
use Flugg\Responder\Http\Resource;
use Flugg\Responder\Http\SuccessResponse;
use Flugg\Responder\Tests\UnitTestCase;

/**
 * Unit tests for the [Flugg\Responder\Http\SuccessResponse] class.
 *
 * @see \Flugg\Responder\Http\SuccessResponse
 */
class SuccessResponseTest extends UnitTestCase
{
    /**
     * Class being tested.
     *
     * @var \Flugg\Responder\Http\SuccessResponse
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
     * Assert that [setResource] and [resource] sets and gets resource respectively.
     */
    public function testSetAndGetResource()
    {
        $result = $this->response->setResource($resource = new Resource([]));

        $this->assertSame($this->response, $result);
        $this->assertEquals($resource, $this->response->resource());
    }

    /**
     * Assert that [setPaginator] and [paginator] sets and gets paginator respectively.
     */
    public function testSetAndGetPaginator()
    {
        $this->response->setPaginator($paginator = mock(Paginator::class));

        $this->assertEquals($paginator, $this->response->paginator());
    }

    /**
     * Assert that [setCursorPaginator] and [cursorPaginator] sets and gets cursor paginator respectively.
     */
    public function testSetAndGetCursorPaginator()
    {
        $this->response->setCursorPaginator($paginator = mock(CursorPaginator::class));

        $this->assertEquals($paginator, $this->response->cursorPaginator());
    }

    /**
     * Assert that [setStatus] and [status] sets and gets status codes respectively.
     */
    public function testSetAndGetStatusCode()
    {
        $this->response->setStatus($status = 201);

        $this->assertEquals($status, $this->response->status());
    }

    /**
     * Assert that [setStatus] throws an exception when given a non-successful status code.
     */
    public function testSetStatusThrowsExceptionForInvalidStatusCodes()
    {
        $this->expectException(InvalidStatusCodeException::class);

        $this->response->setStatus(400);
    }

    /**
     * Assert that [setHeaders] and [headers] sets and gets status codes respectively.
     */
    public function testSetAndGetHeaders()
    {
        $this->response->setHeaders($headers = ['x-foo' => 123]);

        $this->assertEquals($headers, $this->response->headers());
    }

    /**
     * Assert that [setMeta] and [meta] sets and gets meta data respectively.
     */
    public function testSetAndGetMeta()
    {
        $this->response->setMeta($meta = ['foo' => 123]);

        $this->assertEquals($meta, $this->response->meta());
    }
}
