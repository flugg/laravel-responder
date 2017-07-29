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
     * The [NullSerializer] class being tested.
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
     * Assert that the [collection] method returns the given data untouched.
     */
    public function testCollectionMethodShouldReturnDataDirectly()
    {
        $result = $this->serializer->collection(null, $data = ['foo' => 1]);

        $this->assertEquals($data, $result);
    }

    /**
     * Assert that the [item] method returns the given data untouched.
     */
    public function testItemMethodShouldReturnDataDirectly()
    {
        $result = $this->serializer->item(null, $data = ['foo' => 1]);

        $this->assertEquals($data, $result);
    }

    /**
     * Assert that the [null] method returns an empty array.
     */
    public function testNullMethodShouldReturnAnEmptyArray()
    {
        $result = $this->serializer->null();

        $this->assertEquals([], $result);
    }

    /**
     * Assert that the [meta] method returns an empty array.
     */
    public function testMetaMethodShouldReturnAnEmptyArray()
    {
        $result = $this->serializer->meta($meta = ['foo' => 1]);

        $this->assertEquals([], $result);
    }

    /**
     * Assert that the [paginator] method returns an empty array.
     */
    public function testPaginatorMethodShouldReturnAnEmptyArray()
    {
        $result = $this->serializer->paginator($paginator = Mockery::mock(PaginatorInterface::class));

        $this->assertEquals([], $result);
    }

    /**
     * Assert that the [cursor] method returns an empty array.
     */
    public function testCursorMethodShouldReturnAnEmptyArray()
    {
        $result = $this->serializer->cursor($cursor = Mockery::mock(CursorInterface::class));

        $this->assertEquals([], $result);
    }

    /**
     * Assert that the [mergeIncludes] method merges relations.
     */
    public function testMergeIncludesMethodShouldMergeRelations()
    {
        $result = $this->serializer->mergeIncludes($data = ['foo' => 1], $relations = ['bar' => 2]);

        $this->assertEquals(['foo' => 1, 'bar' => 2], $result);
    }
}