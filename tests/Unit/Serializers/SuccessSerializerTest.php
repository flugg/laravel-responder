<?php

namespace Flugg\Responder\Tests\Unit\Serializers;

use Flugg\Responder\Serializers\SuccessSerializer;
use Flugg\Responder\Tests\TestCase;
use League\Fractal\Pagination\CursorInterface;
use League\Fractal\Pagination\PaginatorInterface;
use Mockery;

/**
 * Unit tests for the [Flugg\Responder\Serializers\SuccessSerializer] class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
final class SuccessSerializerTest extends TestCase
{
    /**
     * The [SuccessSerializer] class being tested.
     *
     * @var \Flugg\Responder\Serializers\SuccessSerializer
     */
    protected $serializer;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->serializer = new SuccessSerializer();
    }

    /**
     * Assert that the [collection] method wraps the data in a [data] field.
     */
    public function testCollectionMethodShouldWrapDataInADataField(): void
    {
        $result = $this->serializer->collection(null, $data = ['foo' => 1]);

        $this->assertEquals(['data' => $data], $result);
    }

    /**
     * Assert that the [item] method wraps the data in a [data] field.
     */
    public function testItemMethodShouldWrapDataInADataField(): void
    {
        $result = $this->serializer->item(null, $data = ['foo' => 1]);

        $this->assertEquals(['data' => $data], $result);
    }

    /**
     * Assert that the [null] method wraps [null] in a [data] field.
     */
    public function testNullMethodShouldWrapNullInADataField(): void
    {
        $result = $this->serializer->null();

        $this->assertEquals(['data' => null], $result);
    }

    /**
     * Assert that the [meta] method returns the given data untouched.
     */
    public function testMetaMethodShouldReturnDataDirectly(): void
    {
        $result = $this->serializer->meta($meta = ['foo' => 1]);

        $this->assertEquals($meta, $result);
    }

    /**
     * Assert that the [paginator] method returns a formatted pagination meta data.
     */
    public function testPaginatorMethodShouldReturnAFormattedArray(): void
    {
        $paginator = Mockery::mock(PaginatorInterface::class);
        $paginator->shouldReceive('getTotal')->andReturn($total = 15);
        $paginator->shouldReceive('getCount')->andReturn($count = 10);
        $paginator->shouldReceive('getPerPage')->andReturn($perPage = 5);
        $paginator->shouldReceive('getCurrentPage')->andReturn($currentPage = 2);
        $paginator->shouldReceive('getLastPage')->andReturn($lastPage = 3);
        $paginator->shouldReceive('getUrl')->with(1)->andReturn($previousUrl = 'foo.com/1');
        $paginator->shouldReceive('getUrl')->with(3)->andReturn($nextUrl = 'foo.com/3');
        $result = $this->serializer->paginator($paginator);

        $this->assertEquals([
            'pagination' => [
                'total' => $total,
                'count' => $count,
                'perPage' => $perPage,
                'currentPage' => $currentPage,
                'totalPages' => $lastPage,
                'links' => [
                    'previous' => $previousUrl,
                    'next' => $nextUrl,
                ],
            ],
        ], $result);
    }

    /**
     * Assert that the [paginator] method returns a formatted cursor meta data.
     */
    public function testCursorMethodShouldReturnAFormattedArray(): void
    {
        $cursor = Mockery::mock(CursorInterface::class);
        $cursor->shouldReceive('getPrev')->andReturn($previous = 1);
        $cursor->shouldReceive('getCurrent')->andReturn($current = 2);
        $cursor->shouldReceive('getNext')->andReturn($next = 3);
        $cursor->shouldReceive('getCount')->andReturn($count = 2);
        $result = $this->serializer->cursor($cursor);

        $this->assertEquals([
            'cursor' => [
                'current' => $current,
                'previous' => $previous,
                'next' => $next,
                'count' => $count,
            ],
        ], $result);
    }

    /**
     * Assert that the [mergeIncludes] method merges relations and strips away extra data fields.
     */
    public function testMergeIncludesMethodShouldMergeRelationsAndStripDataFields(): void
    {
        $result = $this->serializer->mergeIncludes($data = ['foo' => 1], $relations = ['bar' => ['data' => 2]]);

        $this->assertEquals(['foo' => 1, 'bar' => 2], $result);
    }
}