<?php

namespace Flugg\Responder\Tests\Unit\Http\Formatters;

use Flugg\Responder\Contracts\Pagination\CursorPaginator;
use Flugg\Responder\Contracts\Pagination\Paginator;
use Flugg\Responder\Contracts\Validation\Validator;
use Flugg\Responder\Http\ErrorResponse;
use Flugg\Responder\Http\Formatters\SimpleFormatter;
use Flugg\Responder\Http\SuccessResponse;
use Flugg\Responder\Tests\UnitTestCase;

/**
 * Unit tests for the [Flugg\Responder\Http\Formatters\SimpleFormatter] class.
 *
 * @see \Flugg\Responder\Http\Formatters\SimpleFormatter
 */
class SimpleFormatterTest extends UnitTestCase
{
    /**
     * Class being tested.
     *
     * @var \Flugg\Responder\Http\Formatters\SimpleFormatter
     */
    protected $formatter;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->formatter = new SimpleFormatter;
    }

    /**
     * Assert that [success] formats success responses from a success response value object.
     */
    public function testSuccessMethodFormatsSuccessResponses()
    {
        $response = mock(SuccessResponse::class);
        $response->allows([
            'data' => $data = ['foo' => 123],
            'meta' => $meta = ['bar' => 456],
            'paginator' => null,
            'cursorPaginator' => null,
        ]);

        $result = $this->formatter->success($response);

        $this->assertEquals(array_merge([
            'data' => $data,
        ], $meta), $result);
        $response->shouldHaveReceived('data');
        $response->shouldHaveReceived('meta');
    }

    /**
     * Assert that [paginator] attaches pagination meta data to response data.
     */
    public function testPaginatorMethodAttachesPagination()
    {
        $response = mock(SuccessResponse::class);
        $response->allows([
            'data' => $data = ['foo' => 123],
            'meta' => [],
            'paginator' => $paginator = mock(Paginator::class),
            'cursorPaginator' => null,
        ]);
        $paginator->allows([
            'count' => $count = 10,
            'total' => $total = 15,
            'perPage' => $perPage = 5,
            'currentPage' => $currentPage = 2,
            'lastPage' => $lastPage = 3,
        ]);
        $paginator->allows('url')->with(1)->andReturn($firstPageUrl = 'example.com?page=1');
        $paginator->allows('url')->with(2)->andReturn($selfUrl = 'example.com?page=2');
        $paginator->allows('url')->with(3)->andReturn($lastPageUrl = 'example.com?page=3');

        $result = $this->formatter->success($response);

        $this->assertEquals([
            'data' => $data,
            'pagination' => [
                'count' => $count,
                'total' => $total,
                'perPage' => $perPage,
                'currentPage' => $currentPage,
                'lastPage' => $lastPage,
                'links' => [
                    'self' => $selfUrl,
                    'first' => $firstPageUrl,
                    'last' => $lastPageUrl,
                    'prev' => $firstPageUrl,
                    'next' => $lastPageUrl,
                ],
            ],
        ], $result);
    }

    /**
     * Assert that [paginator] excludes previous and next links when there's only one page.
     */
    public function testPaginatorMethodOmitsUndefinedLinks()
    {
        $response = mock(SuccessResponse::class);
        $response->allows([
            'data' => $data = ['foo' => 123],
            'meta' => [],
            'paginator' => $paginator = mock(Paginator::class),
            'cursorPaginator' => null,
        ]);
        $paginator->allows([
            'count' => $count = 5,
            'total' => $total = 5,
            'perPage' => $perPage = 5,
            'currentPage' => $currentPage = 1,
            'lastPage' => $lastPage = 1,
        ]);
        $paginator->allows('url')->with(1)->andReturn($firstPageUrl = 'example.com?page=1');
        $paginator->allows('url')->with(2)->andReturn($selfUrl = 'example.com?page=1');
        $paginator->allows('url')->with(3)->andReturn($lastPageUrl = 'example.com?page=1');

        $result = $this->formatter->success($response);

        $this->assertEquals([
            'data' => $data,
            'pagination' => [
                'count' => $count,
                'total' => $total,
                'perPage' => $perPage,
                'currentPage' => $currentPage,
                'lastPage' => $lastPage,
                'links' => [
                    'self' => $selfUrl,
                    'first' => $firstPageUrl,
                    'last' => $lastPageUrl,
                ],
            ],
        ], $result);
    }

    /**
     * Assert that [cursor] attaches cursor pagination meta data to response data.
     */
    public function testCursorMethodAttachesCursorPagination()
    {
        $response = mock(SuccessResponse::class);
        $response->allows([
            'data' => $data = ['foo' => 123],
            'meta' => [],
            'paginator' => null,
            'cursorPaginator' => $paginator = mock(CursorPaginator::class),
        ]);
        $paginator->allows([
            'current' => $current = 10,
            'previous' => $previous = 5,
            'next' => $next = 15,
            'count' => $count = 30,
        ]);

        $result = $this->formatter->success($response);

        $this->assertEquals([
            'data' => $data,
            'cursor' => [
                'current' => $current,
                'previous' => $previous,
                'next' => $next,
                'count' => $count,
            ],
        ], $result);
    }

    /**
     * Assert that [error] formats error responses from an error response value object.
     */
    public function testErrorMethodFormatsSuccessResponses()
    {
        $response = mock(ErrorResponse::class);
        $response->allows([
            'code' => $code = 'error_occured',
            'message' => $message = 'An error has occured.',
            'meta' => ['foo' => 123],
            'validator' => null
        ]);

        $result = $this->formatter->error($response);

        $this->assertEquals([
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
            'foo' => 123,
        ], $result);
        $response->shouldHaveReceived('code');
        $response->shouldHaveReceived('message');
    }

    /**
     * Assert that [error] excludes error messages if it's not set.
     */
    public function testErrorMethodOmitsUndefinedMessage()
    {
        $response = mock(ErrorResponse::class);
        $response->allows([
            'code' => $code = 'error_occured',
            'message' => null,
            'meta' => [],
            'validator' => null
        ]);

        $result = $this->formatter->error($response);

        $this->assertEquals([
            'error' => [
                'code' => $code,
            ],
        ], $result);
    }

    /**
     * Assert that [validator] attaches validation meta data to response data.
     */
    public function testValidationMethodAttachesValidation()
    {
        $response = mock(ErrorResponse::class);
        $response->allows([
            'code' => $code = 'error_occured',
            'message' => null,
            'meta' => [],
            'validator' => $validator = mock(Validator::class)
        ]);
        $validator->allows([
            'failed' => ['foo', 'bar.baz'],
            'errors' => [
                'foo' => ['min', 'email'],
                'bar.baz' => ['required'],
            ],
            'messages' => [
                'foo.min' => $minMessage = 'Must be larger',
                'foo.email' => $emailMessage = 'Invalid email',
                'bar.baz.required' => $requiredMessage = 'Required field',
            ],
        ]);

        $result = $this->formatter->error($response);

        $this->assertEquals([
            'error' => [
                'code' => $code,
                'fields' => [
                    'foo' => [
                        ['rule' => 'min', 'message' => $minMessage],
                        ['rule' => 'email', 'message' => $emailMessage],
                    ],
                    'bar.baz' => [
                        ['rule' => 'required', 'message' => $requiredMessage],
                    ],
                ],
            ],
        ], $result);
    }
}
