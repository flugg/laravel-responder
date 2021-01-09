<?php

namespace Flugg\Responder\Tests\Unit\Http\Formatters;

use Flugg\Responder\Http\Formatters\SimpleFormatter;
use Flugg\Responder\Tests\UnitTestCase;

/**
 * Unit tests for the [SimpleFormatter] class.
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
     * Assert that [success] formats success responses with an item resource.
     */
    public function testSuccessMethodFormatsSuccessResponsesWithItem()
    {
        $item = $this->mockItem($data = ['foo' => 1], $key = 'baz');
        $response = $this->mockSuccessResponse($item, $meta = ['bar' => 2]);

        $result = $this->formatter->success($response->reveal());

        $this->assertSame(array_merge([
            $key => $data,
        ], $meta), $result);
    }

    /**
     * Assert that [success] formats success responses with a resource collection.
     */
    public function testSuccessMethodFormatsSuccessResponsesWithCollection()
    {
        $collection = $this->mockCollection([
            $this->mockItem($data1 = ['foo' => 1]),
            $this->mockItem($data2 = ['bar' => 2]),
        ], $key = 'baz');
        $response = $this->mockSuccessResponse($collection, $meta = ['bar' => 2]);

        $result = $this->formatter->success($response->reveal());

        $this->assertSame(array_merge([
            $key => [$data1, $data2],
        ], $meta), $result);
    }

    /**
     * Assert that [success] formats success responses with a "data" wrapper if no resource key is set.
     */
    public function testSuccessMethodWrapperDefaultsToData()
    {
        $item = $this->mockItem($data = ['foo' => 1]);
        $response = $this->mockSuccessResponse($item, $meta = ['bar' => 2]);

        $result = $this->formatter->success($response->reveal());

        $this->assertSame(array_merge([
            'data' => $data,
        ], $meta), $result);
    }

    /**
     * Assert that [success] formats success responses with related resources.
     */
    public function testSuccessMethodFormatsRelations()
    {
        $item = $this->mockItem($data = ['foo' => 1], null, [
            'foo' => $this->mockItem($relatedItemData = ['foo' => 1], null, [
                'bar' => $this->mockItem($nestedItemData = ['bar' => 2]),
            ]),
            'bar' => $this->mockCollection([
                'baz' => $this->mockItem($collectionItemData = ['baz' => 3]),
            ]),
        ]);
        $response = $this->mockSuccessResponse($item, $meta = ['bar' => 2]);

        $result = $this->formatter->success($response->reveal());

        $this->assertSame(array_merge([
            'data' => array_merge($data, [
                'foo' => array_merge($relatedItemData, [
                    'bar' => $nestedItemData,
                ]),
                'bar' => [
                    'baz' => $collectionItemData,
                ],
            ]),
        ], $meta), $result);
    }

    /**
     * Assert that [success] method attaches pagination metadata to response data.
     */
    public function testSuccessMethodAttachesPagination()
    {
        $item = $this->mockItem($data = ['foo' => 1], $key = 'baz');
        $paginator = $this->mockPaginator($count = 10, $total = 15, $perPage = 5, $currentPage = 2, $lastPage = 3);
        $paginator->url(1)->willReturn($firstPageUrl = 'example.com?page=1');
        $paginator->url(2)->willReturn($selfUrl = 'example.com?page=2');
        $paginator->url(3)->willReturn($lastPageUrl = 'example.com?page=3');
        $response = $this->mockSuccessResponse($item);
        $response->paginator()->willReturn($paginator);

        $result = $this->formatter->success($response->reveal());

        $this->assertSame([
            $key => $data,
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
     * Assert that [success] method excludes previous and next pagination links when there's only one page.
     */
    public function testSucessMethodOmitsPreviousAndNextLinksWhenNotSet()
    {
        $item = $this->mockItem($data = ['foo' => 1], $key = 'baz');
        $paginator = $this->mockPaginator($count = 5, $total = 5, $perPage = 5, $currentPage = 1, $lastPage = 1);
        $paginator->url(1)->willReturn($url = 'example.com?page=1');
        $paginator->url(2)->willReturn($url);
        $paginator->url(3)->willReturn($url);
        $response = $this->mockSuccessResponse($item);
        $response->paginator()->willReturn($paginator);

        $result = $this->formatter->success($response->reveal());

        $this->assertSame([
            $key => $data,
            'pagination' => [
                'count' => $count,
                'total' => $total,
                'perPage' => $perPage,
                'currentPage' => $currentPage,
                'lastPage' => $lastPage,
                'links' => [
                    'self' => $url,
                    'first' => $url,
                    'last' => $url,
                ],
            ],
        ], $result);
    }

    /**
     * Assert that [success] method attaches cursor pagination metadata to response data.
     */
    public function testSuccessMethodAttachesCursorPagination()
    {
        $item = $this->mockItem($data = ['foo' => 1], $key = 'baz');
        $paginator = $this->mockCursor($count = 30, $current = 10, $previous = 5, $next = 15);
        $response = $this->mockSuccessResponse($item);
        $response->cursor()->willReturn($paginator);

        $result = $this->formatter->success($response->reveal());

        $this->assertSame([
            $key => $data,
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
    public function testErrorMethodFormatsErrorResponses()
    {
        $response = $this->mockErrorResponse($code = 'foo', $message = 'A foo error.', $meta = ['foo' => 1]);

        $result = $this->formatter->error($response->reveal());

        $this->assertSame(array_merge([
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ], $meta), $result);
    }

    /**
     * Assert that [error] excludes error messages if it's not set.
     */
    public function testErrorMethodOmitsUndefinedMessage()
    {
        $response = $this->mockErrorResponse($code = 'foo');

        $result = $this->formatter->error($response->reveal());

        $this->assertSame([
            'error' => [
                'code' => $code,
            ],
        ], $result);
    }

    /**
     * Assert that [validator] attaches validation metadata to response data.
     */
    public function testValidationMethodAttachesValidation()
    {
        $validator = $this->mockValidator(
            ['foo', 'bar.baz'],
            ['foo' => ['min', 'email'], 'bar.baz' => ['required']],
            [
                'foo.min' => $minMessage = 'Must be larger',
                'foo.email' => $emailMessage = 'Invalid email',
                'bar.baz.required' => $requiredMessage = 'Required field',
            ]
        );
        $response = $this->mockErrorResponse($code = 'foo');
        $response->validator()->willReturn($validator);

        $result = $this->formatter->error($response->reveal());

        $this->assertSame([
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
