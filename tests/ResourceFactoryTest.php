<?php

namespace Flugg\Responder\Tests;

use Flugg\Responder\ResourceFactory;
use InvalidArgumentException;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\NullResource;

/**
 * Collection of unit tests testing the [\Flugg\Responder\ResourceFactory] class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class ResourceFactoryTest extends TestCase
{
    /**
     * Test that the [make] method returns a [\League\Fractal\Resource\NullResouce] instance
     * when no data is given.
     */
    public function testMakeMethodShouldReturnNullResourceWhenGivenNull()
    {
        $data = null;

        $resource = (new ResourceFactory())->make($data);

        $this->assertInstanceOf(NullResource::class, $resource);
    }

    /**
     * Test that the [make] method returns a [\League\Fractal\Resource\Item] instance when
     * you pass in an Eloquent model.
     */
    public function testMakeMethodShouldReturnItemResourceWhenGivenModel()
    {
        $data = $this->makeModel();

        $resource = (new ResourceFactory())->make($data);

        $this->assertInstanceOf(Item::class, $resource);
        $this->assertEquals($data, $resource->getData());
    }

    /**
     * Test that the [make] method returns a [\League\Fractal\Resource\Collection] instance
     * when you pass in an array.
     */
    public function testMakeMethodShouldReturnCollectionResourceWhenGivenArray()
    {
        $data = [$this->makeModel()];

        $resource = (new ResourceFactory())->make($data);

        $this->assertInstanceOf(Collection::class, $resource);
        $this->assertEquals($data, $resource->getData());
    }

    /**
     * Test that the [make] method returns a [\League\Fractal\Resource\Collection] instance
     * when you pass in an empty array.
     */
    public function testMakeMethodShouldReturnCollectionResourceWhenGivenEmptyArray()
    {
        $data = [];

        $resource = (new ResourceFactory())->make($data);

        $this->assertInstanceOf(Collection::class, $resource);
    }

    /**
     * Test that the [make] method returns a [\League\Fractal\Resource\Collection] instance
     * when you pass in a collection.
     */
    public function testMakeMethodShouldReturnCollectionResourceWhenGivenCollection()
    {
        $data = collect([$this->makeModel()]);

        $resource = (new ResourceFactory())->make($data);

        $this->assertInstanceOf(Collection::class, $resource);
        $this->assertEquals($data, $resource->getData());
    }

    /**
     * Test that the [make] method returns a [\League\Fractal\Resource\NullResource] instance
     * when you pass in an empty collection.
     */
    public function testMakeMethodShouldReturnCollectionResourceWhenGivenEmptyCollection()
    {
        $data = collect();

        $resource = (new ResourceFactory())->make($data);

        $this->assertInstanceOf(Collection::class, $resource);
    }

    /**
     * Test that the [make] method returns a [\League\Fractal\Resource\Collection] instance
     * when you pass in an Eloquent query builder.
     */
    public function testMakeMethodShouldReturnCollectionResourceWhenGivenQueryBuilder()
    {
        $data = $this->mockBuilder([$this->makeModel()]);

        $resource = (new ResourceFactory())->make($data);

        $this->assertInstanceOf(Collection::class, $resource);
        $this->assertEquals($data->get(), $resource->getData());
    }

    /**
     * Test that the [make] method returns a [\League\Fractal\Resource\Collection] instance
     * when you pass in an Eloquent query builder with no results.
     */
    public function testMakeMethodShouldReturnCollectionResourceWhenGivenEmptyQueryBuilder()
    {
        $data = $this->mockBuilder([]);

        $resource = (new ResourceFactory())->make($data);

        $this->assertInstanceOf(Collection::class, $resource);
    }

    /**
     * Test that the [make] method returns a [\League\Fractal\Resource\Collection] instance
     * when you pass in a paginator.
     */
    public function testMakeMethodShouldReturnCollectionResourceWhenGivenPaginator()
    {
        $data = $this->mockBuilder([$this->makeModel()])->paginate();

        $resource = (new ResourceFactory())->make($data);

        $this->assertInstanceOf(Collection::class, $resource);
        $this->assertEquals($data->getCollection(), $resource->getData());
        $this->assertEquals(new IlluminatePaginatorAdapter($data), $resource->getPaginator());
    }

    /**
     * Test that the [make] method returns a [\League\Fractal\Resource\Collection] instance
     * when you pass in a paginator with no data.
     */
    public function testMakeMethodShouldReturnCollectionResourceWhenGivenEmptyPaginator()
    {
        $data = $this->mockBuilder([])->paginate();

        $resource = (new ResourceFactory())->make($data);

        $this->assertInstanceOf(Collection::class, $resource);
    }

    /**
     * Test that the [make] method .
     */
    public function testMakeMethodShouldAppendParametersToUrlWhenGivenPaginator()
    {
        $data = $this->mockBuilder([$this->makeModel()])->paginate();
        $parameters = ['foo' => 1, 'page' => 1];

        $resource = (new ResourceFactory())->make($data, $parameters);

        $this->assertEquals('/?foo=1&page=2', $resource->getPaginator()->getUrl(2));
    }

    /**
     * Test that the [make] method returns a [\League\Fractal\Resource\Collection] instance
     * when you pass in a relationship instance.
     */
    public function testMakeMethodShouldReturnCollectionResourceWhenGivenRelation()
    {
        $data = $this->mockRelation([$this->makeModel()]);

        $resource = (new ResourceFactory())->make($data);

        $this->assertInstanceOf(Collection::class, $resource);
        $this->assertEquals($data->get(), $resource->getData());
    }

    /**
     * Test that the [make] method returns a [\League\Fractal\Resource\Collection] instance
     * when you pass in an instance of [\Illuminate\Database\Eloquent\Relations\Relation]
     * which contains no data.
     *
     * @test
     * @covers \Flugg\Responder\ResourceFactory::make
     * @covers \Flugg\Responder\ResourceFactory::getMakeMethod
     * @covers \Flugg\Responder\ResourceFactory::makeFromRelation
     */
    public function makeMethodShouldReturnCollectionResourceWhenGivenEmptyRelation()
    {
        // Arrange...
        $data = $this->mockRelation(null);

        // Act...
        $resource = (new ResourceFactory())->make($data);

        // Assert...
        $this->assertInstanceOf(Collection::class, $resource);
    }

    /**
     * Test that the [make] method returns a [\League\Fractal\Resource\Item] instance when
     * you pass in an instance of [\Illuminate\Database\Eloquent\Relations\Pivot].
     *
     * @test
     * @covers \Flugg\Responder\ResourceFactory::make
     * @covers \Flugg\Responder\ResourceFactory::getMakeMethod
     * @covers \Flugg\Responder\ResourceFactory::makeFromPivot
     */
    public function makeMethodShouldReturnCollectionResourceWhenGivenPivot()
    {
        // Arrange...
        $data = $this->mockPivot();

        // Act...
        $resource = (new ResourceFactory())->make($data);

        // Assert...
        $this->assertInstanceOf(Item::class, $resource);
        $this->assertEquals($data, $resource->getData());
    }

    /**
     * Test that the [make] method throws an [\InvalidArgumentException] when passing in
     * data of unsupported data type.
     *
     * @test
     * @covers \Flugg\Responder\ResourceFactory::make
     * @covers \Flugg\Responder\ResourceFactory::getMakeMethod
     */
    public function makeMethodShouldThrowExceptionWhenGivenInvalidData()
    {
        // Arrange...
        $this->expectException(InvalidArgumentException::class);

        // Act...
        (new ResourceFactory())->make('foo');
    }
}