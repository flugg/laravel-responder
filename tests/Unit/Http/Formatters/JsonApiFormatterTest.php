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
     * Assert that [success] formats success responses with related resources.
     */
    public function testSuccessMethodFormatsRelations()
    {
        $item = new Item($data = ['id' => 1, 'foo' => 2], $key = 'foo', [
            'foo' => new Item($relatedItemData = ['id' => 3, 'foo' => 4], $relatedItemKey = 'bar', [
                'bar' => new Item($nestedItemData = ['id' => 5, 'bar' => 6], $nestedItemKey = 'baz'),
            ]),
            'bar' => new Collection([
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
                    'bar' => [
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
     * Assert that [success] method attaches pagination metadata to response data.
     */
    public function testSuccessMethodAttachesPagination()
    {
        $item = new Item($data = []);
        $paginator = $this->mockPaginator($count = 10, $total = 15, $perPage = 5, $currentPage = 2, $lastPage = 3);
        $paginator->url(1)->willReturn($firstPageUrl = 'example.com?page=1');
        $paginator->url(2)->willReturn($selfUrl = 'example.com?page=2');
        $paginator->url(3)->willReturn($lastPageUrl = 'example.com?page=3');
        $response = (new SuccessResponse($item))->setPaginator($paginator->reveal());

        $result = $this->formatter->success($response);

        $this->assertSame([
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
     * Assert that [success] method excludes previous and next pagination links when there's only one page.
     */
    public function testSucessMethodOmitsPreviousAndNextLinksWhenNotSet()
    {
        $item = new Item($data = []);
        $paginator = $this->mockPaginator($count = 5, $total = 5, $perPage = 5, $currentPage = 1, $lastPage = 1);
        $paginator->url(1)->willReturn($url = 'example.com?page=1');
        $paginator->url(2)->willReturn($url);
        $paginator->url(3)->willReturn($url);
        $response = (new SuccessResponse($item))->setPaginator($paginator->reveal());

        $result = $this->formatter->success($response);

        $this->assertSame([
            'data' => $data,
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
        $item = new Item($data = []);
        $cursor = $this->mockCursor($count = 30, $current = 10, $previous = 5, $next = 15);
        $response = (new SuccessResponse($item))->setCursor($cursor->reveal());

        $result = $this->formatter->success($response);

        $this->assertSame([
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
    public function testErrorMethodFormatsErrorResponses()
    {
        $response = (new ErrorResponse)
            ->setCode($code = 'foo')
            ->setMessage($message = 'bar')
            ->setMeta($meta = ['foo' => 1]);

        $result = $this->formatter->error($response);

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
        $response = (new ErrorResponse)->setCode($code = 'foo');

        $result = $this->formatter->error($response);

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
        $response = (new ErrorResponse)->setCode($code = 'foo')->setValidator($validator->reveal());

        $result = $this->formatter->error($response);

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
