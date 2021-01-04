<?php

namespace Flugg\Responder\Tests\Unit\Http\Formatters;

use Flugg\Responder\Contracts\Pagination\CursorPaginator;
use Flugg\Responder\Contracts\Pagination\Paginator;
use Flugg\Responder\Contracts\Validation\Validator;
use Flugg\Responder\Http\ErrorResponse;
use Flugg\Responder\Http\Formatters\SimpleFormatter;
use Flugg\Responder\Http\Resources\Collection;
use Flugg\Responder\Http\Resources\Item;
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
     * Assert that [success] formats success responses where the resource is an resource item.
     */
    public function testSuccessMethodFormatsSuccessResponsesWithItem()
    {
        $response = mock(SuccessResponse::class);
        $response->allows([
            'resource' => $item = mock(Item::class),
            'meta' => $meta = ['bar' => 456],
            'paginator' => null,
            'cursorPaginator' => null,
        ]);
        $item->allows([
            'data' => $data = ['foo' => 123],
            'key' => $key = 'baz',
            'relations' => []
        ]);

        $result = $this->formatter->success($response);

        $this->assertEquals(array_merge([
            $key => $data,
        ], $meta), $result);
        $response->shouldHaveReceived('resource');
        $response->shouldHaveReceived('meta');
    }

    /**
     * Assert that [success] formats success responses where the resource is a resource collection.
     */
    public function testSuccessMethodFormatsSuccessResponsesWithCollection()
    {
        $response = mock(SuccessResponse::class);
        $response->allows([
            'resource' => $collection = mock(Collection::class),
            'meta' => $meta = ['bar' => 456],
            'paginator' => null,
            'cursorPaginator' => null,
        ]);
        $collection->allows([
            'items' => [$item1 = mock(Item::class), $item2 = mock(Item::class)],
            'key' => $key = 'baz',
            'relations' => []
        ]);
        $item1->allows([
            'data' => $data1 = ['foo' => 123],
            'relations' => []
        ]);
        $item2->allows([
            'data' => $data2 = ['bar' => 456],
            'relations' => []
        ]);

        $result = $this->formatter->success($response);

        $this->assertEquals(array_merge([
            $key => [$data1, $data2],
        ], $meta), $result);
        $response->shouldHaveReceived('resource');
        $response->shouldHaveReceived('meta');
    }

    /**
     * Assert that [success] formats success responses with a "data" wrapper if no resource key is set.
     */
    public function testSuccessMethodWrapperDefaultsToData()
    {
        $response = mock(SuccessResponse::class);
        $response->allows([
            'resource' => $item = mock(Item::class),
            'meta' => $meta = ['bar' => 456],
            'paginator' => null,
            'cursorPaginator' => null,
        ]);
        $item->allows([
            'data' => $data = ['foo' => 123],
            'key' => null,
            'relations' => []
        ]);

        $result = $this->formatter->success($response);

        $this->assertEquals(array_merge([
            'data' => $data,
        ], $meta), $result);
    }

    /**
     * Assert that [success] formats success responses with related resources.
     */
    public function testSuccessMethodFormatsRelations()
    {
        $response = mock(SuccessResponse::class);
        $response->allows([
            'resource' => $item = mock(Item::class),
            'meta' => $meta = ['bar' => 456],
            'paginator' => null,
            'cursorPaginator' => null,
        ]);
        $item->allows([
            'data' => $data = ['foo' => 123],
            'key' => null,
            'relations' => [
                $relatedItemKey = 'bar' => $relatedItem = mock(Item::class),
                $relatedCollectionKey = 'baz' => $relatedCollection = mock(Collection::class),
            ]
        ]);
        $relatedItem->allows([
            'data' => $relatedItemData = ['bar' => 456],
            'relations' => [
                $nestedItem1Key = 'foo' => $nestedItem1 = mock(Item::class)
            ]
        ]);
        $relatedCollection->allows([
            'items' => [$nestedItem2 = mock(Item::class), $nestedItem3 = mock(Item::class)],
            'relations' => []
        ]);
        $nestedItem1->allows(['data' => $nestedItem1Data = ['foo' => 123], 'relations' => []]);
        $nestedItem2->allows(['data' => $nestedItem2Data = ['foo' => 123], 'relations' => []]);
        $nestedItem3->allows(['data' => $nestedItem3Data = ['bar' => 456], 'relations' => []]);

        $result = $this->formatter->success($response);

        $this->assertEquals(array_merge([
            'data' => array_merge($data, [
                $relatedItemKey => array_merge($relatedItemData, [
                    $nestedItem1Key => $nestedItem1Data
                ]),
                $relatedCollectionKey => [
                    $nestedItem2Data,
                    $nestedItem3Data,
                ]
            ]),
        ], $meta), $result);
    }

    /**
     * Assert that [success] method attaches pagination metadata to response data.
     */
    public function testSuccessMethodAttachesPagination()
    {
        $response = mock(SuccessResponse::class);
        $response->allows([
            'resource' => $item = mock(Item::class),
            'meta' => [],
            'paginator' => $paginator = mock(Paginator::class),
            'cursorPaginator' => null,
        ]);
        $item->allows([
            'data' => $data = ['foo' => 123],
            'key' => $key = 'baz',
            'relations' => []
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
    public function testSucessMethodOmitsUndefinedLinksForPagination()
    {
        $response = mock(SuccessResponse::class);
        $response->allows([
            'resource' => $item = mock(Item::class),
            'meta' => [],
            'paginator' => $paginator = mock(Paginator::class),
            'cursorPaginator' => null,
        ]);
        $item->allows([
            'data' => $data = ['foo' => 123],
            'key' => $key = 'baz',
            'relations' => []
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
                ],
            ],
        ], $result);
    }

    /**
     * Assert that [success] method attaches cursor pagination metadata to response data.
     */
    public function testSuccessMethodAttachesCursorPagination()
    {
        $response = mock(SuccessResponse::class);
        $response->allows([
            'resource' => $item = mock(Item::class),
            'meta' => [],
            'paginator' => null,
            'cursorPaginator' => $paginator = mock(CursorPaginator::class),
        ]);
        $item->allows([
            'data' => $data = ['foo' => 123],
            'key' => $key = 'baz',
            'relations' => []
        ]);
        $paginator->allows([
            'current' => $current = 10,
            'previous' => $previous = 5,
            'next' => $next = 15,
            'count' => $count = 30,
        ]);

        $result = $this->formatter->success($response);

        $this->assertEquals([
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
     * Assert that [validator] attaches validation metadata to response data.
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
