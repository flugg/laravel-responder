<?php

namespace Flugg\Responder\Tests\Unit;

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
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class SimpleFormatterTest extends UnitTestCase
{
    /**
     * The class being tested.
     *
     * @var SimpleFormatter
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
     * Assert that the [success] method formats success responses from a success response object.
     */
    public function testSuccessMethodFormatsSuccessResponses()
    {
        $response = mock(SuccessResponse::class);
        $response->allows([
            'data' => $data = ['foo' => 123],
            'meta' => $meta = ['bar' => 456],
        ]);

        $result = $this->formatter->success($response);

        $this->assertEquals(array_merge([
            'data' => $data,
        ], $meta), $result);
        $response->shouldHaveReceived('data');
        $response->shouldHaveReceived('meta');
    }

    /**
     * Assert that the [paginator] method formats success responses with pagination meta data.
     */
    public function testPaginatorMethodFormatsPaginatedResponses()
    {
        $paginator = mock(Paginator::class);
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

        $result = $this->formatter->paginator($data = [
            'data' => ['foo' => 123],
            'bar' => 456,
        ], $paginator);

        $this->assertEquals(array_merge($data, [
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
        ]), $result);
    }

    /**
     * Assert that the [paginator] method excludes previous and next links when there are none.
     */
    public function testPaginatorMethodCanOmitPreviousAndNextLinks()
    {
        $paginator = mock(Paginator::class);
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

        $result = $this->formatter->paginator($data = [
            'data' => ['foo' => 123],
            'bar' => 456,
        ], $paginator);

        $this->assertEquals(array_merge($data, [
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
        ]), $result);
    }

    /**
     * Assert that the [cursor] method formats success responses with cursor pagination meta data.
     */
    public function testCursorMethodFormatsCursorPaginatedResponses()
    {
        $paginator = mock(CursorPaginator::class);
        $paginator->allows([
            'current' => $current = 10,
            'previous' => $previous = 5,
            'next' => $next = 15,
            'count' => $count = 30,
        ]);

        $result = $this->formatter->cursor($data = [
            'data' => ['foo' => 123],
            'bar' => 456,
        ], $paginator);

        $this->assertEquals(array_merge($data, [
            'cursor' => [
                'current' => $current,
                'previous' => $previous,
                'next' => $next,
                'count' => $count,
            ],
        ]), $result);
    }

    /**
     * Assert that the [error] method formats error responses from an error response object.
     */
    public function testErrorMethodFormatsSuccessResponses()
    {
        $response = mock(ErrorResponse::class);
        $response->allows([
            'errorCode' => $code = 'error_occured',
            'message' => $message = 'An error has occured.',
        ]);

        $result = $this->formatter->error($response);

        $this->assertEquals([
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ], $result);
        $response->shouldHaveReceived('errorCode');
        $response->shouldHaveReceived('message');
    }

    /**
     * Assert that the [error] method excludes error messages if not set.
     */
    public function testErrorMethodCanOmitMessage()
    {
        $response = mock(ErrorResponse::class);
        $response->allows([
            'errorCode' => $code = 'error_occured',
            'message' => null,
        ]);

        $result = $this->formatter->error($response);

        $this->assertEquals([
            'error' => [
                'code' => $code,
            ],
        ], $result);
    }

    /**
     * Assert that the [validator] method formats error responses with validation errors.
     */
    public function testValidationMethodFormatsValidationErrrorResponses()
    {
        $validator = mock(Validator::class);
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

        $result = $this->formatter->validator([
            'error' => $error = [
                'code' => 'error_occured',
                'message' => 'An error has occured',
            ],
        ], $validator);

        $this->assertEquals([
            'error' => array_merge($error, [
                'fields' => [
                    'foo' => [
                        ['rule' => 'min', 'message' => $minMessage],
                        ['rule' => 'email', 'message' => $emailMessage],
                    ],
                    'bar.baz' => [
                        ['rule' => 'required', 'message' => $requiredMessage],
                    ],
                ],
            ]),
        ], $result);
    }
}
