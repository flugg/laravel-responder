<?php

namespace Flugg\Responder\Tests;

use Flugg\Responder\ResourceFactory;
use InvalidArgumentException;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\NullResource;

/**
 * Collection of unit tests for the [\Flugg\Responder\ResourceFactory] class.
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
     *
     * @test
     * @covers \Flugg\Responder\ResourceFactory::make
     */
    public function makeMethodShouldReturnNullResourceWhenGivenNull()
    {
        // Arrange...
        $data = null;

        // Act...
        $resource = (new ResourceFactory())->make($data);

        // Assert...
        $this->assertInstanceOf(NullResource::class, $resource);
    }

    /**
     * Test that the [make] method returns a [\League\Fractal\Resource\Item] instance when
     * you pass in a single model.
     *
     * @test
     * @covers \Flugg\Responder\ResourceFactory::make
     * @covers \Flugg\Responder\ResourceFactory::getMakeMethod
     * @covers \Flugg\Responder\ResourceFactory::makeFromModel
     */
    public function makeMethodShouldReturnItemResourceWhenGivenModel()
    {
        // Arrange...
        $data = $this->makeModel();

        // Act...
        $resource = (new ResourceFactory())->make($data);

        // Assert...
        $this->assertInstanceOf(Item::class, $resource);
        $this->assertEquals($data, $resource->getData());
    }

    /**
     * Test that the [make] method returns a [\League\Fractal\Resource\Collection] instance
     * when you pass in an array.
     *
     * @test
     * @covers \Flugg\Responder\ResourceFactory::make
     * @covers \Flugg\Responder\ResourceFactory::makeFromArray
     */
    public function makeMethodShouldReturnCollectionResourceWhenGivenArray()
    {
        // Arrange...
        $data = [$this->makeModel()];

        // Act...
        $resource = (new ResourceFactory())->make($data);

        // Assert...
        $this->assertInstanceOf(Collection::class, $resource);
        $this->assertEquals($data, $resource->getData());
    }

    /**
     * Test that the [make] method returns a [\League\Fractal\Resource\Collection] instance
     * when you pass in an empty array.
     *
     * @test
     * @covers \Flugg\Responder\ResourceFactory::make
     * @covers \Flugg\Responder\ResourceFactory::makeFromArray
     */
    public function makeMethodShouldReturnCollectionResourceWhenGivenEmptyArray()
    {
        // Arrange...
        $data = [];

        // Act...
        $resource = (new ResourceFactory())->make($data);

        // Assert...
        $this->assertInstanceOf(Collection::class, $resource);
    }

    /**
     * Test that the [make] method returns a [\League\Fractal\Resource\Collection] instance
     * when you pass in an Illuminate collection.
     *
     * @test
     * @covers \Flugg\Responder\ResourceFactory::make
     * @covers \Flugg\Responder\ResourceFactory::getMakeMethod
     * @covers \Flugg\Responder\ResourceFactory::makeFromCollection
     */
    public function makeMethodShouldReturnCollectionResourceWhenGivenCollection()
    {
        // Arrange...
        $data = collect([$this->makeModel()]);

        // Act...
        $resource = (new ResourceFactory())->make($data);

        // Assert...
        $this->assertInstanceOf(Collection::class, $resource);
        $this->assertEquals($data, $resource->getData());
    }

    /**
     * Test that the [make] method returns a [\League\Fractal\Resource\NullResource] instance
     * when you pass in an empty Illuminate collection.
     *
     * @test
     * @covers \Flugg\Responder\ResourceFactory::make
     * @covers \Flugg\Responder\ResourceFactory::getMakeMethod
     * @covers \Flugg\Responder\ResourceFactory::makeFromCollection
     */
    public function makeMethodShouldReturnCollectionResourceWhenGivenEmptyCollection()
    {
        // Arrange...
        $data = collect();

        // Act...
        $resource = (new ResourceFactory())->make($data);

        // Assert...
        $this->assertInstanceOf(Collection::class, $resource);}

    /**
     * Test that the [make] method returns a [\League\Fractal\Resource\Collection] instance
     * when you pass in an Eloquent query builder.
     *
     * @test
     * @covers \Flugg\Responder\ResourceFactory::make
     * @covers \Flugg\Responder\ResourceFactory::getMakeMethod
     * @covers \Flugg\Responder\ResourceFactory::makeFromBuilder
     */
    public function makeMethodShouldReturnCollectionResourceWhenGivenQueryBuilder()
    {
        // Arrange...
        $data = $this->mockBuilder([$this->makeModel()]);

        // Act...
        $resource = (new ResourceFactory())->make($data);

        // Assert...
        $this->assertInstanceOf(Collection::class, $resource);
        $this->assertEquals($data->get(), $resource->getData());
    }

    /**
     * Test that the [make] method returns a [\League\Fractal\Resource\Collection] instance
     * when you pass in an Eloquent query builder which gives no results.
     *
     * @test
     * @covers \Flugg\Responder\ResourceFactory::make
     * @covers \Flugg\Responder\ResourceFactory::getMakeMethod
     * @covers \Flugg\Responder\ResourceFactory::makeFromBuilder
     */
    public function makeMethodShouldReturnCollectionResourceWhenGivenEmptyQueryBuilder()
    {
        // Arrange...
        $data = $this->mockBuilder([]);

        // Act...
        $resource = (new ResourceFactory())->make($data);

        // Assert...
        $this->assertInstanceOf(Collection::class, $resource);
    }

    /**
     * Test that the [make] method returns a [\League\Fractal\Resource\Collection] instance
     * when you pass in an instance of [\Illuminate\Pagination\LengthAwarePaginator].
     *
     * @test
     * @covers \Flugg\Responder\ResourceFactory::make
     * @covers \Flugg\Responder\ResourceFactory::getMakeMethod
     * @covers \Flugg\Responder\ResourceFactory::makeFromPaginator
     */
    public function makeMethodShouldReturnCollectionResourceWhenGivenPaginator()
    {
        // Arrange...
        $builder = $this->mockBuilderWithPaginator([$this->makeModel()]);
        $data = $builder->paginate();

        // Act...
        $resource = (new ResourceFactory())->make($data);

        // Assert...
        $this->assertInstanceOf(Collection::class, $resource);
        $this->assertEquals($data->getCollection(), $resource->getData());
        $this->assertEquals(new IlluminatePaginatorAdapter($data), $resource->getPaginator());
    }

    /**
     * Test that the [make] method returns a [\League\Fractal\Resource\Collection] instance
     * when you pass in an instance of [\Illuminate\Pagination\LengthAwarePaginator] with
     * no data.
     *
     * @test
     * @covers \Flugg\Responder\ResourceFactory::make
     * @covers \Flugg\Responder\ResourceFactory::getMakeMethod
     * @covers \Flugg\Responder\ResourceFactory::makeFromPaginator
     */
    public function makeMethodShouldReturnCollectionResourceWhenGivenEmptyPaginator()
    {
        // Arrange...
        $builder = $this->mockBuilderWithPaginator([]);
        $data = $builder->paginate();

        // Act...
        $resource = (new ResourceFactory())->make($data);

        // Assert...
        $this->assertInstanceOf(Collection::class, $resource);
    }

    /**
     * Test that the [make] method returns a [\League\Fractal\Resource\Collection] instance
     * when you pass in an instance of [\Illuminate\Pagination\LengthAwarePaginator].
     *
     * @test
     * @covers \Flugg\Responder\ResourceFactory::make
     * @covers \Flugg\Responder\ResourceFactory::getMakeMethod
     * @covers \Flugg\Responder\ResourceFactory::makeFromPaginator
     */
    public function makeMethodShouldAppendParametersToUrlWhenGivenPaginator()
    {
        // Arrange...
        $builder = $this->mockBuilderWithPaginator([$this->makeModel()]);
        $data = $builder->paginate();
        $parameters = ['foo' => 1, 'page' => 1];

        // Act...
        $resource = (new ResourceFactory())->make($data, $parameters);

        // Assert...
        $this->assertEquals('/?foo=1&page=2', $resource->getPaginator()->getUrl(2));
    }

    /**
     * Test that the [make] method returns a [\League\Fractal\Resource\Collection] instance
     * when you pass in an instance of [\Illuminate\Database\Eloquent\Relations\Relation].
     *
     * @test
     * @covers \Flugg\Responder\ResourceFactory::make
     * @covers \Flugg\Responder\ResourceFactory::getMakeMethod
     * @covers \Flugg\Responder\ResourceFactory::makeFromRelation
     */
    public function makeMethodShouldReturnCollectionResourceWhenGivenRelation()
    {
        // Arrange...
        $data = $this->mockRelation([$this->makeModel()]);

        // Act...
        $resource = (new ResourceFactory())->make($data);

        // Assert...
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