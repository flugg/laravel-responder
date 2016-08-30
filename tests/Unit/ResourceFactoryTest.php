<?php

namespace Flugg\Responder\Tests\Unit;

use Flugg\Responder\ResourceFactory;
use Flugg\Responder\Tests\TestCase;
use InvalidArgumentException;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\NullResource;

/**
 * Collection of unit tests testing [\Flugg\Responder\ResourceFactory].
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
     * Test that the [make] method returns a [\League\Fractal\Resource\NullResource] instance
     * when you pass in an empty array.
     *
     * @test
     */
    public function makeMethodShouldReturnNullResourceWhenGivenEmptyArray()
    {
        // Arrange...
        $data = [];

        // Act...
        $resource = (new ResourceFactory())->make($data);

        // Assert...
        $this->assertInstanceOf(NullResource::class, $resource);
    }

    /**
     * Test that the [make] method returns a [\League\Fractal\Resource\Collection] instance
     * when you pass in an Illuminate collection.
     *
     * @test
     */
    public function makeMethodShouldReturnCollectionResourceWhenGivenCollection()
    {
        // Arrange...
        $data = collect([$this->makeModel()]);

        // Act...
        $resource = (new ResourceFactory())->make($data);

        // Assert...
        $this->assertInstanceOf(Collection::class, $resource);
        $this->assertEquals($data->all(), $resource->getData());
    }

    /**
     * Test that the [make] method returns a [\League\Fractal\Resource\NullResource] instance
     * when you pass in an empty Illuminate collection.
     *
     * @test
     */
    public function makeMethodShouldReturnNullResourceWhenGivenEmptyCollection()
    {
        // Arrange...
        $data = collect([]);

        // Act...
        $resource = (new ResourceFactory())->make($data);

        // Assert...
        $this->assertInstanceOf(NullResource::class, $resource);
    }

    /**
     * Test that the [make] method returns a [\League\Fractal\Resource\Collection] instance
     * when you pass in an Eloquent query builder.
     *
     * @test
     */
    public function makeMethodShouldReturnCollectionResourceWhenGivenQueryBuilder()
    {
        // Arrange...
        $data = $this->mockBuilder([$this->makeModel()]);

        // Act...
        $resource = (new ResourceFactory())->make($data);

        // Assert...
        $this->assertInstanceOf(Collection::class, $resource);
        $this->assertEquals($data->get()->all(), $resource->getData());
    }

    /**
     * Test that the [make] method returns a [\League\Fractal\Resource\NullResouce] instance
     * when you pass in an Eloquent query builder which gives no results.
     *
     * @test
     */
    public function makeMethodShouldReturnNullResourceWhenGivenEmptyQueryBuilder()
    {
        // Arrange...
        $data = $this->mockBuilder([]);

        // Act...
        $resource = (new ResourceFactory())->make($data);

        // Assert...
        $this->assertInstanceOf(NullResource::class, $resource);
    }

    /**
     * Test that the [make] method returns a [\League\Fractal\Resource\Collection] instance
     * when you pass in an instance of [\Illuminate\Pagination\LengthAwarePaginator].
     *
     * @test
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
        $this->assertEquals($data->getCollection()->all(), $resource->getData());
        $this->assertEquals(new IlluminatePaginatorAdapter($data), $resource->getPaginator());
    }

    /**
     * Test that the [make] method returns a [\League\Fractal\Resource\NullResource] instance
     * when you pass in an instance of [\Illuminate\Pagination\LengthAwarePaginator] with
     * no data.
     *
     * @test
     */
    public function makeMethodShouldReturnNullResourceWhenGivenEmptyPaginator()
    {
        // Arrange...
        $builder = $this->mockBuilderWithPaginator([]);
        $data = $builder->paginate();

        // Act...
        $resource = (new ResourceFactory())->make($data);

        // Assert...
        $this->assertInstanceOf(NullResource::class, $resource);
    }

    /**
     * Test that the [make] method returns a [\League\Fractal\Resource\Collection] instance
     * when you pass in an instance of [\Illuminate\Database\Eloquent\Relations\Relation].
     *
     * @test
     */
    public function makeMethodShouldReturnCollectionResourceWhenGivenRelation()
    {
        // Arrange...
        $data = $this->mockRelation([$this->makeModel()]);

        // Act...
        $resource = (new ResourceFactory())->make($data);

        // Assert...
        $this->assertInstanceOf(Collection::class, $resource);
        $this->assertEquals($data->get()->all(), $resource->getData());
    }

    /**
     * Test that the [make] method returns a [\League\Fractal\Resource\NullResource] instance
     * when you pass in an instance of [\Illuminate\Database\Eloquent\Relations\Relation]
     * which contains no data.
     *
     * @test
     */
    public function makeMethodShouldReturnNullResourceWhenGivenEmptyRelation()
    {
        // Arrange...
        $data = $this->mockRelation(null);

        // Act...
        $resource = (new ResourceFactory())->make($data);

        // Assert...
        $this->assertInstanceOf(NullResource::class, $resource);
    }

    /**
     * Test that the [make] method returns a [\League\Fractal\Resource\Item] instance when
     * you pass in an instance of [\Illuminate\Database\Eloquent\Relations\Pivot].
     *
     * @test
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
     */
    public function makeMethodShouldThrowExceptionWhenGivenInvalidData()
    {
        // Arrange...
        $this->expectException(InvalidArgumentException::class);

        // Act...
        (new ResourceFactory())->make('foo');
    }
}