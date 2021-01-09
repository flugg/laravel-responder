<?php

namespace Flugg\Responder\Tests\Unit\Http;

use Flugg\Responder\Contracts\Pagination\Cursor;
use Flugg\Responder\Contracts\Pagination\CursorPaginator;
use Flugg\Responder\Contracts\Pagination\Paginator;
use Flugg\Responder\Exceptions\InvalidStatusCodeException;
use Flugg\Responder\Http\Resources\Resource;
use Flugg\Responder\Http\SuccessResponse;
use Flugg\Responder\Tests\UnitTestCase;

/**
 * Unit tests for the [SuccessResponse] class.
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
    protected function setUp(): void
    {
        parent::setUp();

        $this->response = new SuccessResponse;
    }

    /**
     * Assert that [setResource] and [resource] sets and gets resource respectively.
     */
    public function testSetAndGetResource()
    {
        $resource = $this->prophesize(Resource::class);

        $result = $this->response->setResource($resource->reveal());

        $this->assertSame($this->response, $result);
        $this->assertSame($resource->reveal(), $this->response->resource());
    }

    /**
     * Assert that [setPaginator] and [paginator] sets and gets paginator respectively.
     */
    public function testSetAndGetPaginator()
    {
        $paginator = $this->prophesize(Paginator::class);

        $this->response->setPaginator($paginator->reveal());

        $this->assertSame($paginator->reveal(), $this->response->paginator());
    }

    /**
     * Assert that [setCursor] and [cursor] sets and gets cursor paginator respectively.
     */
    public function testSetAndGetCursor()
    {
        $paginator = $this->prophesize(CursorPaginator::class);

        $this->response->setCursor($paginator->reveal());

        $this->assertSame($paginator->reveal(), $this->response->cursor());
    }

    /**
     * Assert that [setStatus] and [status] sets and gets status codes respectively.
     */
    public function testSetAndGetStatusCode()
    {
        $this->response->setStatus($status = 201);

        $this->assertSame($status, $this->response->status());
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
     * Assert that [setHeaders] and [headers] sets and gets headers respectively.
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
