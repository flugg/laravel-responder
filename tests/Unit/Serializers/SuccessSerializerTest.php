<?php

namespace Flugg\Responder\Tests\Unit\Serializers;

use Flugg\Responder\Serializers\SuccessSerializer;
use Flugg\Responder\Tests\TestCase;
use League\Fractal\Pagination\CursorInterface;
use League\Fractal\Pagination\PaginatorInterface;
use League\Fractal\Resource\Item;
use Mockery;

/**
 * Unit tests for the [Flugg\Responder\Serializers\SuccessSerializerTest] class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class SuccessSerializerTest extends TestCase
{
    /**
     * The error serializer being tested.
     *
     * @var \Flugg\Responder\Serializers\SuccessSerializerTest
     */
    protected $serializer;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->serializer = new SuccessSerializer();
    }

    /**
     *
     */
    public function testCollectionMethodShouldWrapDataInADataArray()
    {
        $result = $this->serializer->collection(null, $data = ['foo' => 1]);

        $this->assertEquals(['data' => $data], $result);
    }

    /**
     *
     */
    public function testItemMethodShouldWrapDataInADataArray()
    {
        $result = $this->serializer->item(null, $data = ['foo' => 1]);

        $this->assertEquals(['data' => $data], $result);
    }

    /**
     *
     */
    public function testNullMethodShouldReturnNullWrappedInADataArray()
    {
        $result = $this->serializer->null();

        $this->assertEquals(['data' => null], $result);
    }

    /**
     *
     */
    public function testMetaMethodShouldReturnDataUntouched()
    {
        $result = $this->serializer->meta($meta = ['foo' => 1]);

        $this->assertEquals($meta, $result);
    }

    /**
     *
     */
    public function testPaginatorMethodShouldReturnAFormattedArray()
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
     *
     */
    public function testCursorMethodShouldReturnAFormattedArray()
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
     *
     */
    public function testSideloadIncludesMethodShouldReturnTrue()
    {
        $result = $this->serializer->sideloadIncludes();

        $this->assertTrue($result);
    }

    /**
     *
     */
    public function testMergeIncludesMethodShouldMergeRelationsAndStripAwayDataKeys()
    {
        $data = ['foo' => 1];
        $relations = ['bar' => ['data' => 2]];

        $result = $this->serializer->mergeIncludes($data, $relations);

        $this->assertEquals(['foo' => 1, 'bar' => 2], $result);
    }

    /**
     *
     */
    public function testIncludedDataMethodShouldReturnAnEmptyArray()
    {
        $result = $this->serializer->includedData($resource = new Item, ['foo' => 1]);

        $this->assertEquals([], $result);
    }
}