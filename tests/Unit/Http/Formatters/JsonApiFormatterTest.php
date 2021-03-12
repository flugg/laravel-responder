<?php

namespace Flugg\Responder\Tests\Unit\Http\Formatters;

use Flugg\Responder\Http\ErrorResponse;
use Flugg\Responder\Http\Formatters\JsonApiFormatter;
use Flugg\Responder\Http\Resources\Collection;
use Flugg\Responder\Http\Resources\Item;
use Flugg\Responder\Http\Resources\Primitive;
use Flugg\Responder\Http\Resources\Resource;
use Flugg\Responder\Http\SuccessResponse;
use Flugg\Responder\Tests\UnitTestCase;
use Illuminate\Support\Arr;
use InvalidArgumentException;

/**
 * Unit tests for the [JsonApiFormatter] class.
 *
 * @see \Flugg\Responder\Http\Formatters\JsonApiFormatter
 */
class JsonApiFormatterTest extends UnitTestCase
{
    /**
     * Class being tested.
     *
     * @var \Flugg\Responder\Http\Formatters\JsonApiFormatter
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

        $this->formatter = new JsonApiFormatter;
    }

    /**
     * Assert that [success] throws exception when a resource is missing an id field.
     */
    public function testSuccessMethodThrowsExceptionForMissingId()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('JSON API resource objects must have an ID');

        $item = new Item(['foo' => 2], 'bar');
        $response = (new SuccessResponse($item))->setMeta($meta = ['bar' => 2]);

        $result = $this->formatter->success($response);
    }

    /**
     * Assert that [success] formats success responses with an item resource.
     */
    public function testSuccessMethodFormatsSuccessResponsesWithItem()
    {
        $item = new Item($data = ['id' => 1, 'foo' => 2], $key = 'bar');
        $response = (new SuccessResponse($item))->setMeta($meta = ['bar' => 2]);

        $result = $this->formatter->success($response);

        $this->assertSame([
            'data' => [
                'type' => $key,
                'id' => $data['id'],
                'attributes' => Arr::except($data, 'id'),
            ],
            'meta' => $meta,
        ], $result);
    }

    /**
     * Assert that [success] formats success responses with a resource collection.
     */
    public function testSuccessMethodFormatsSuccessResponsesWithCollection()
    {
        $collection = new Collection([
            new Item($data1 = ['id' => 1, 'foo' => 2]),
            new Item($data2 = ['id' => 3, 'bar' => 4]),
        ], $key = 'baz');
        $response = (new SuccessResponse($collection))->setMeta($meta = ['baz' => 3]);

        $result = $this->formatter->success($response);

        $this->assertSame([
            'data' => [
                [
                    'type' => $key,
                    'id' => $data1['id'],
                    'attributes' => Arr::except($data1, 'id'),
                ],
                [
                    'type' => $key,
                    'id' => $data2['id'],
                    'attributes' => Arr::except($data2, 'id'),
                ],
            ],
            'meta' => $meta,
        ], $result);
    }

    /**
     * Assert that [success] throws an exception when given a primitive resource class.
     */
    public function testSuccessMethodThrowsExceptionForPrimitiveResource()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported resource class');

        $primitive = new Primitive(123);
        $response = (new SuccessResponse($primitive));

        $this->formatter->success($response);
    }

    /**
     * Assert that [success] throws an exception when given an invalid resource class.
     */
    public function testSuccessMethodThrowsExceptionForInvalidResource()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported resource class');

        $resource = $this->mock(Resource::class);
        $response = (new SuccessResponse($resource->reveal()));

        $this->formatter->success($response);
    }

    /**
     * Assert that [success] throws an exception when given an invalid related resource class.
     */
    public function testSuccessMethodThrowsExceptionForInvalidRelatedResource()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported nested resource class');

        $item = new Item(['id' => 1, 'foo' => 2], 'foo', [
            'foo' => $this->mock(Resource::class)->reveal(),
        ]);
        $response = (new SuccessResponse($item));

        $this->formatter->success($response);
    }

    /**
     * Assert that [success] formats success responses with a resource item and related resources.
     */
    public function testSuccessMethodFormatsItemWithRelations()
    {
        $item = new Item($data = ['id' => 1, 'foo' => 2], $key = 'foo', [
            'foo' => new Item($relatedItemData = ['id' => 3, 'foo' => 4], $relatedItemKey = 'bar', [
                'bar' => new Item($nestedItemData = ['id' => 5, 'bar' => 6], $nestedItemKey = 'baz'),
            ]),
            'baz' => new Collection([
                new Item($collectionItemData1 = ['id' => 7, 'baz' => 8]),
                new Item($collectionItemData2 = ['id' => 9, 'qux' => 10]),
            ], $collectionKey = 'qux'),
        ]);
        $response = (new SuccessResponse($item));

        $result = $this->formatter->success($response);

        $this->assertSame([
            'data' => [
                'type' => $key,
                'id' => $data['id'],
                'attributes' => Arr::except($data, 'id'),
                'relationships' => [
                    'foo' => [
                        'data' => [
                            'type' => $relatedItemKey,
                            'id' => $relatedItemData['id'],
                        ],
                    ],
                    'baz' => [
                        'data' => [
                            [
                                'type' => $collectionKey,
                                'id' => $collectionItemData1['id'],
                            ],
                            [
                                'type' => $collectionKey,
                                'id' => $collectionItemData2['id'],
                            ],
                        ],
                    ],
                ],
            ],
            'included' => [
                [
                    'type' => $relatedItemKey,
                    'id' => $relatedItemData['id'],
                    'attributes' => Arr::except($relatedItemData, 'id'),
                    'relationships' => [
                        'bar' => [
                            'data' => [
                                'type' => $nestedItemKey,
                                'id' => $nestedItemData['id'],
                            ],
                        ],
                    ],
                ],
                [
                    'type' => $nestedItemKey,
                    'id' => $nestedItemData['id'],
                    'attributes' => Arr::except($nestedItemData, 'id'),
                ],
                [
                    'type' => $collectionKey,
                    'id' => $collectionItemData1['id'],
                    'attributes' => Arr::except($collectionItemData1, 'id'),
                ],
                [
                    'type' => $collectionKey,
                    'id' => $collectionItemData2['id'],
                    'attributes' => Arr::except($collectionItemData2, 'id'),
                ],
            ],
        ], $result);
    }

    /**
     * Assert that [success] formats success responses with a resource collection and related resources.
     */
    public function testSuccessMethodFormatsCollectionWithRelations()
    {
        $item = new Collection([
            new Item($collectionItemData1 = ['id' => 1, 'foo' => 2], null, [
                'foo' => new Item($relatedItemData = ['id' => 3, 'foo' => 4], $relatedItemKey = 'bar', [
                    'bar' => new Item($nestedItemData = ['id' => 5, 'bar' => 6], $nestedItemKey = 'baz'),
                ]),
            ]),
            new Item($collectionItemData2 = ['id' => 7, 'qux' => 8]),
        ], $key = 'a');
        $response = (new SuccessResponse($item));

        $result = $this->formatter->success($response);

        $this->assertSame([
            'data' => [
                [
                    'type' => $key,
                    'id' => $collectionItemData1['id'],
                    'attributes' => Arr::except($collectionItemData1, 'id'),
                    'relationships' => [
                        'foo' => [
                            'data' => [
                                'type' => $relatedItemKey,
                                'id' => $relatedItemData['id'],
                            ],
                        ],
                    ],
                ],
                [
                    'type' => $key,
                    'id' => $collectionItemData2['id'],
                    'attributes' => Arr::except($collectionItemData2, 'id'),
                ],
            ],
            'included' => [
                [
                    'type' => $relatedItemKey,
                    'id' => $relatedItemData['id'],
                    'attributes' => Arr::except($relatedItemData, 'id'),
                    'relationships' => [
                        'bar' => [
                            'data' => [
                                'type' => $nestedItemKey,
                                'id' => $nestedItemData['id'],
                            ],
                        ],
                    ],
                ],
                [
                    'type' => $nestedItemKey,
                    'id' => $nestedItemData['id'],
                    'attributes' => Arr::except($nestedItemData, 'id'),
                ],
            ],
        ], $result);
    }

    /**
     * Assert that [success] removes any duplicates from the includes list.
     */
    public function testSuccessMethodOnlyReturnsUniqueIncludes()
    {
        $relatedItem = new Item($relatedItemData = ['id' => 1, 'foo' => 2], $relatedItemKey = 'foo');
        $item = new Collection([
            new Item($collectionItemData1 = ['id' => 3, 'bar' => 4], null, ['foo' => $relatedItem]),
            new Item($collectionItemData2 = ['id' => 5, 'baz' => 6], null, ['bar' => $relatedItem]),
            new Item($collectionItemData3 = ['id' => 7, 'bar' => 8], null, ['baz' => $relatedItem]),
        ], $key = 'bar');
        $response = (new SuccessResponse($item));

        $result = $this->formatter->success($response);

        $this->assertSame([
            'data' => [
                [
                    'type' => $key,
                    'id' => $collectionItemData1['id'],
                    'attributes' => Arr::except($collectionItemData1, 'id'),
                    'relationships' => [
                        'foo' => [
                            'data' => [
                                'type' => $relatedItemKey,
                                'id' => $relatedItemData['id'],
                            ],
                        ],
                    ],
                ],
                [
                    'type' => $key,
                    'id' => $collectionItemData2['id'],
                    'attributes' => Arr::except($collectionItemData2, 'id'),
                    'relationships' => [
                        'bar' => [
                            'data' => [
                                'type' => $relatedItemKey,
                                'id' => $relatedItemData['id'],
                            ],
                        ],
                    ],
                ],
                ['type' => $key,
                    'id' => $collectionItemData3['id'],
                    'attributes' => Arr::except($collectionItemData3, 'id'),
                    'relationships' => [
                        'baz' => [
                            'data' => [
                                'type' => $relatedItemKey,
                                'id' => $relatedItemData['id'],
                            ],
                        ],
                    ],
                ],
            ],
            'included' => [
                [
                    'type' => $relatedItemKey,
                    'id' => $relatedItemData['id'],
                    'attributes' => Arr::except($relatedItemData, 'id'),
                ],
            ],
        ], $result);
    }

    /**
     * Assert that [success] sorts all included resources alphabetically by type and ID.
     */
    public function testSuccessMethodSortsIncludedResources()
    {
        $item = new Item(['id' => 1], 'foo', [
            'foo' => new Item(['id' => 2], 'a', [
                'bar' => new Item(['id' => 3], 'b'),
            ]),
            'baz' => new Collection([
                new Item(['id' => 3]),
                new Item(['id' => 1], null, [
                    'qux' => new Item(['id' => 3], 'a'),
                ]),
                new Item(['id' => 2]),
            ], 'c'),
            'qux' => new Item(['id' => 4], 'b', [
                'bar' => new Item(['id' => 1], 'a'),
            ]),
        ]);
        $response = (new SuccessResponse($item));

        $result = $this->formatter->success($response);

        $this->assertSame([
            'data' => [
                'type' => 'foo',
                'id' => 1,
                'attributes' => [],
                'relationships' => [
                    'foo' => [
                        'data' => [
                            'type' => 'a',
                            'id' => 2,
                        ],
                    ],
                    'baz' => [
                        'data' => [
                            [
                                'type' => 'c',
                                'id' => 3,
                            ],
                            [
                                'type' => 'c',
                                'id' => 1,
                            ],
                            [
                                'type' => 'c',
                                'id' => 2,
                            ],
                        ],
                    ],
                    'qux' => [
                        'data' => [
                            'type' => 'b',
                            'id' => 4,
                        ],
                    ],
                ],
            ],
            'included' => [
                [
                    'type' => 'a',
                    'id' => 1,
                    'attributes' => [],
                ],
                [
                    'type' => 'a',
                    'id' => 2,
                    'attributes' => [],
                    'relationships' => [
                        'bar' => [
                            'data' => [
                                'type' => 'b',
                                'id' => 3,
                            ],
                        ],
                    ],
                ],
                [
                    'type' => 'a',
                    'id' => 3,
                    'attributes' => [],
                ],
                [
                    'type' => 'b',
                    'id' => 3,
                    'attributes' => [],
                ],
                [
                    'type' => 'b',
                    'id' => 4,
                    'attributes' => [],
                    'relationships' => [
                        'bar' => [
                            'data' => [
                                'type' => 'a',
                                'id' => 1,
                            ],
                        ],
                    ],
                ],
                [
                    'type' => 'c',
                    'id' => 1,
                    'attributes' => [],
                    'relationships' => [
                        'qux' => [
                            'data' => [
                                'type' => 'a',
                                'id' => 3,
                            ],
                        ],
                    ],
                ],
                [
                    'type' => 'c',
                    'id' => 2,
                    'attributes' => [],
                ],
                [
                    'type' => 'c',
                    'id' => 3,
                    'attributes' => [],
                ],
            ],
        ], $result);
    }

    /**
     * Assert that [success] method attaches pagination metadata to response data.
     */
    public function testSuccessMethodAttachesPagination()
    {
        $item = new Item($data = ['id' => 1], $key = 'bar');
        $paginator = $this->mockPaginator($count = 10, $total = 15, $perPage = 5, $currentPage = 2, $lastPage = 3);
        $paginator->url(1)->willReturn($firstPageUrl = 'example.com?page=1');
        $paginator->url(2)->willReturn($selfUrl = 'example.com?page=2');
        $paginator->url(3)->willReturn($lastPageUrl = 'example.com?page=3');
        $response = (new SuccessResponse($item))->setPaginator($paginator->reveal());

        $result = $this->formatter->success($response);

        $this->assertSame([
            'data' => [
                'type' => $key,
                'id' => $data['id'],
                'attributes' => [],
            ],
            'pagination' => [
                'count' => $count,
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $currentPage,
                'total_pages' => $lastPage,
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
        $item = new Item($data = ['id' => 1], $key = 'bar');
        $paginator = $this->mockPaginator($count = 5, $total = 5, $perPage = 5, $currentPage = 1, $lastPage = 1);
        $paginator->url(1)->willReturn($url = 'example.com?page=1');
        $paginator->url(2)->willReturn($url);
        $paginator->url(3)->willReturn($url);
        $response = (new SuccessResponse($item))->setPaginator($paginator->reveal());

        $result = $this->formatter->success($response);

        $this->assertSame([
            'data' => [
                'type' => $key,
                'id' => $data['id'],
                'attributes' => [],
            ],
            'pagination' => [
                'count' => $count,
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $currentPage,
                'total_pages' => $lastPage,
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
        $item = new Item($data = ['id' => 1], $key = 'bar');
        $cursor = $this->mockCursor($count = 30, $current = 10, $previous = 5, $next = 15);
        $response = (new SuccessResponse($item))->setCursor($cursor->reveal());

        $result = $this->formatter->success($response);

        $this->assertSame([
            'data' => [
                'type' => $key,
                'id' => $data['id'],
                'attributes' => [],
            ],
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
        $response = (new ErrorResponse)
            ->setCode($code = 'foo')
            ->setMessage($message = 'bar')
            ->setMeta($meta = ['foo' => 1]);

        $result = $this->formatter->error($response);

        $this->assertSame([
            'errors' => [['code' => $code, 'title' => $message]],
            'meta' => $meta,
        ], $result);
    }

    /**
     * Assert that [error] excludes error messages if it's not set.
     */
    public function testErrorMethodOmitsUndefinedMessage()
    {
        $response = (new ErrorResponse)->setCode($code = 'foo');

        $result = $this->formatter->error($response);

        $this->assertSame([
            'errors' => [['code' => $code]],
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
        $response = (new ErrorResponse)
            ->setCode($code = 'foo')
            ->setMessage($message = 'bar')
            ->setValidator($validator->reveal());

        $result = $this->formatter->error($response);

        $this->assertSame([
            'errors' => [
                [
                    'code' => $code,
                    'title' => $message,
                    'detail' => $minMessage,
                    'source' => 'foo',
                ],
                [
                    'code' => $code,
                    'title' => $message,
                    'detail' => $emailMessage,
                    'source' => 'foo',
                ],
                [
                    'code' => $code,
                    'title' => $message,
                    'detail' => $requiredMessage,
                    'source' => 'bar.baz',
                ],
            ],
        ], $result);
    }
}
