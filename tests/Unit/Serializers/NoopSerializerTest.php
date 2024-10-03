<?php

namespace Flugg\Responder\Tests\Unit\Serializers;

use Flugg\Responder\Serializers\NoopSerializer;
use Flugg\Responder\Tests\TestCase;
use League\Fractal\Pagination\CursorInterface;
use League\Fractal\Pagination\PaginatorInterface;
use Mockery;

/**
 * Unit tests for the [Flugg\Responder\Serializers\NoopSerializer] class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
final class NoopSerializerTest extends TestCase
{
    /**
     * The [NoopSerializer] class being tested.
     *
     * @var \Flugg\Responder\Serializers\NoopSerializer
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

        $this->serializer = new NoopSerializer;
    }

    /**
     * Assert that the [collection] method returns the given data untouched.
     */
    public function testCollectionMethodShouldReturnDataDirectly(): void
    {
        $result = $this->serializer->collection(null, $data = ['foo' => 1]);

        $this->assertEquals($data, $result);
    }

    /**
     * Assert that the [item] method returns the given data untouched.
     */
    public function testItemMethodShouldReturnDataDirectly(): void
    {
        $result = $this->serializer->item(null, $data = ['foo' => 1]);

        $this->assertEquals($data, $result);
    }

    /**
     * Assert that the [null] method returns null.
     */
    public function testNullMethodShouldReturnNull(): void
    {
        $result = $this->serializer->null();

        $this->assertEquals(null, $result);
    }

    /**
     * Assert that the [meta] method returns an empty array.
     */
    public function testMetaMethodShouldReturnAnEmptyArray(): void
    {
        $result = $this->serializer->meta($meta = ['foo' => 1]);

        $this->assertEquals([], $result);
    }

    /**
     * Assert that the [paginator] method returns an empty array.
     */
    public function testPaginatorMethodShouldReturnAnEmptyArray(): void
    {
        $result = $this->serializer->paginator($paginator = Mockery::mock(PaginatorInterface::class));

        $this->assertEquals([], $result);
    }

    /**
     * Assert that the [cursor] method returns an empty array.
     */
    public function testCursorMethodShouldReturnAnEmptyArray(): void
    {
        $result = $this->serializer->cursor($cursor = Mockery::mock(CursorInterface::class));

        $this->assertEquals([], $result);
    }

    /**
     * Assert that the [mergeIncludes] method merges relations.
     */
    public function testMergeIncludesMethodShouldMergeRelations(): void
    {
        $result = $this->serializer->mergeIncludes($data = ['foo' => 1], $relations = ['bar' => 2]);

        $this->assertEquals(['foo' => 1, 'bar' => 2], $result);
    }
}