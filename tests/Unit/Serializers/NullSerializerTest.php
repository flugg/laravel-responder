<?php

namespace Flugg\Responder\Tests\Unit\Serializers;

use Flugg\Responder\Serializers\NullSerializer;
use Flugg\Responder\Tests\TestCase;
use League\Fractal\Pagination\CursorInterface;
use League\Fractal\Pagination\PaginatorInterface;
use Mockery;

/**
 * Unit tests for the [Flugg\Responder\Serializers\NullSerializer] class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class NullSerializerTest extends TestCase
{
    /**
     * The null serializer being tested.
     *
     * @var \Flugg\Responder\Serializers\NullSerializer
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

        $this->serializer = new NullSerializer;
    }

    /**
     *
     */
    public function testCollectionMethodShouldReturnDataUntouched()
    {
        $result = $this->serializer->collection(null, $data = ['foo' => 1]);

        $this->assertEquals($data, $result);
    }

    /**
     *
     */
    public function testItemMethodShouldReturnDataUntouched()
    {
        $result = $this->serializer->item(null, $data = ['foo' => 1]);

        $this->assertEquals($data, $result);
    }

    /**
     *
     */
    public function testNullMethodShouldReturnAnEmptyArray()
    {
        $result = $this->serializer->null();

        $this->assertEquals([], $result);
    }

    /**
     *
     */
    public function testMetaMethodShouldReturnAnEmptyArray()
    {
        $result = $this->serializer->meta($meta = ['foo' => 1]);

        $this->assertEquals([], $result);
    }

    /**
     *
     */
    public function testPaginatorMethodShouldReturnAnEmptyArray()
    {
        $result = $this->serializer->paginator($paginator = Mockery::mock(PaginatorInterface::class));

        $this->assertEquals([], $result);
    }

    /**
     *
     */
    public function testCursorMethodShouldReturnAnEmptyArray()
    {
        $result = $this->serializer->cursor($cursor = Mockery::mock(CursorInterface::class));

        $this->assertEquals([], $result);
    }

    /**
     *
     */
    public function testMergeIncludesMethodShouldMergeRelations()
    {
        [$data, $relations] = [['foo' => 1], ['bar' => 2]];

        $result = $this->serializer->mergeIncludes($data, $relations);

        $this->assertEquals(['foo' => 1, 'bar' => 2], $result);
    }
}