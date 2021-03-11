<?php

namespace Flugg\Responder\Tests\Unit\Http\Formatters;

use Flugg\Responder\Http\ErrorResponse;
use Flugg\Responder\Http\Formatters\SimpleFormatter;
use Flugg\Responder\Http\Resources\Collection;
use Flugg\Responder\Http\Resources\Item;
use Flugg\Responder\Http\Resources\Primitive;
use Flugg\Responder\Http\Resources\Resource;
use Flugg\Responder\Http\SuccessResponse;
use Flugg\Responder\Tests\UnitTestCase;
use InvalidArgumentException;

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
        $item = new Item($data = ['foo' => 1]);
        $response = (new SuccessResponse($item))->setMeta($meta = ['bar' => 2]);

        $result = $this->formatter->success($response);

        $this->assertSame(array_merge([
            'data' => $data,
        ], $meta), $result);
    }

    /**
     * Assert that [success] formats success responses with a resource collection.
     */
    public function testSuccessMethodFormatsSuccessResponsesWithCollection()
    {
        $collection = new Collection([
            new Item($data1 = ['foo' => 1]),
            new Item($data2 = ['bar' => 2]),
        ]);
        $response = (new SuccessResponse($collection))->setMeta($meta = ['baz' => 3]);

        $result = $this->formatter->success($response);

        $this->assertSame(array_merge([
            'data' => [$data1, $data2],
        ], $meta), $result);
    }

    /**
     * Assert that [success] formats success responses with a primitive resource.
     */
    public function testSuccessMethodFormatsSuccessResponsesWithPrimitive()
    {
        foreach ([true, 1.0, 1, 'foo', null] as $data) {
            $primitive = new Primitive($data);
            $response = (new SuccessResponse($primitive))->setMeta($meta = ['baz' => 2]);

            $result = $this->formatter->success($response);

            $this->assertSame(array_merge([
                'data' => $data,
            ], $meta), $result);
        }
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
     * Assert that [success] always formats success responses with a "data" wrapper.
     */
    public function testSuccessMethodWrapperDefaultsToData()
    {
        $item = new Item($data = ['foo' => 1], 'bar');
        $response = (new SuccessResponse($item))->setMeta($meta = ['baz' => 2]);

        $result = $this->formatter->success($response);

        $this->assertSame(array_merge([
            'data' => $data,
        ], $meta), $result);
    }

    /**
     * Assert that [success] formats success responses with related resources.
     */
    public function testSuccessMethodFormatsRelations()
    {
        $item = new Item($data = ['foo' => 1], null, [
            'foo' => new Item($relatedItemData = ['foo' => 1], null, [
                'bar' => new Item($nestedItemData = ['bar' => 2]),
            ]),
            'bar' => new Collection([
                new Item($collectionItemData = ['baz' => 3]),
            ]),
        ]);
        $response = (new SuccessResponse($item))->setMeta($meta = ['bar' => 2]);

        $result = $this->formatter->success($response);

        $this->assertSame(array_merge([
            'data' => array_merge($data, [
                'foo' => array_merge($relatedItemData, [
                    'bar' => $nestedItemData,
                ]),
                'bar' => [
                    $collectionItemData,
                ],
            ]),
        ], $meta), $result);
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
                    'previous' => $firstPageUrl,
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
