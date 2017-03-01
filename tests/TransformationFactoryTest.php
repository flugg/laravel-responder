<?php

namespace Flugg\Responder\Tests;

use Closure;
use Flugg\Responder\Exceptions\InvalidTransformerException;
use Flugg\Responder\Transformation;
use Flugg\Responder\TransformationFactory;
use Illuminate\Database\Eloquent\Model;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\NullResource;

/**
 * Collection of unit tests for the [\Flugg\Responder\TransformationFactory] class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 *
 * @covers \Flugg\Responder\TransformationFactory::__construct
 */
class TransformationFactoryTest extends TestCase
{
    /**
     * Test that the [make] method returns a new transformation instance.
     *
     * @test
     * @covers \Flugg\Responder\TransformationFactory::make
     */
    public function makeMethodShouldMakeNewTransformation()
    {
        // Arrange...
        $resourceFactory = $this->mockResourceFactory(new Collection([]));
        $transformationFactory = new TransformationFactory($resourceFactory);

        // Act...
        $transformation = $transformationFactory->make([]);

        // Assert...
        $resourceFactory->shouldHaveReceived('make')->with([])->once();
        $this->assertInstanceOf(Transformation::class, $transformation);
    }

    /**
     * Test that the resource instance on the newly created transformation is set to
     * [\League\Fractal\Resource\NullResource] when given no data to the [make] method.
     *
     * @test
     * @covers \Flugg\Responder\TransformationFactory::make
     * @covers \Flugg\Responder\TransformationFactory::makeEmpty
     */
    public function makeMethodShouldMakeTransformationWithNullResourceWhenGivenNoData()
    {
        // Arrange...
        $resourceFactory = $this->mockResourceFactory(new NullResource);
        $transformationFactory = new TransformationFactory($resourceFactory);

        // Act...
        $transformation = $transformationFactory->make();

        // Assert...
        $resourceFactory->shouldHaveReceived('make')->with(null)->once();
        $this->assertInstanceOf(NullResource::class, $transformation->getResource());
        $this->assertNull($transformation->getModel());
    }

    /**
     * Test that a model is resolved from the data.
     *
     * @test
     * @covers \Flugg\Responder\TransformationFactory::make
     * @covers \Flugg\Responder\TransformationFactory::makeWithModel
     * @covers \Flugg\Responder\TransformationFactory::resolveModel
     */
    public function makeMethodShouldResolveModel()
    {
        // Arrange...
        $model = $this->makeModel();
        $resourceFactory = $this->mockResourceFactory(new Item($model));
        $transformationFactory = new TransformationFactory($resourceFactory);

        // Act...
        $transformation = $transformationFactory->make($model);

        // Assert...
        $this->assertEquals($model, $transformation->getModel());
    }

    /**
     * Test that a model is resolved from the array data.
     *
     * @test
     * @covers \Flugg\Responder\TransformationFactory::make
     * @covers \Flugg\Responder\TransformationFactory::makeWithModel
     * @covers \Flugg\Responder\TransformationFactory::resolveModel
     */
    public function makeMethodShouldResolveModelFromArray()
    {
        // Arrange...
        $model = $this->makeModel();
        $resourceFactory = $this->mockResourceFactory(new Collection([$model]));
        $transformationFactory = new TransformationFactory($resourceFactory);

        // Act...
        $transformation = $transformationFactory->make([$model]);

        // Assert...
        $this->assertEquals($model, $transformation->getModel());
    }

    /**
     * Test that a model is resolved from the collection data.
     *
     * @test
     * @covers \Flugg\Responder\TransformationFactory::make
     * @covers \Flugg\Responder\TransformationFactory::makeWithModel
     * @covers \Flugg\Responder\TransformationFactory::resolveModel
     */
    public function makeMethodShouldResolveModelFromCollection()
    {
        // Arrange...
        $model = $this->makeModel();
        $resourceFactory = $this->mockResourceFactory(new Collection(collect([$model])));
        $transformationFactory = new TransformationFactory($resourceFactory);

        // Act...
        $transformation = $transformationFactory->make(collect([$model]));

        // Assert...
        $this->assertEquals($model, $transformation->getModel());
    }

    /**
     * Test that a transformer is resolved from the model if none is given.
     *
     * @test
     * @covers \Flugg\Responder\TransformationFactory::make
     * @covers \Flugg\Responder\TransformationFactory::makeWithModel
     * @covers \Flugg\Responder\TransformationFactory::resolveTransformer
     */
    public function makeMethodShouldResolveTransformerFromModel()
    {
        // Arrange...
        $model = $this->makeModelWithTransformer($transformer = $this->makeTransformer());
        $resourceFactory = $this->mockResourceFactory(new Item($model));
        $transformationFactory = new TransformationFactory($resourceFactory);

        // Act...
        $transformation = $transformationFactory->make($model);

        // Assert...
        $this->assertSame($transformer, $transformation->getResource()->getTransformer());
    }

    /**
     * Test that a transformer is resolved from the model if none is given.
     *
     * @test
     * @covers \Flugg\Responder\TransformationFactory::make
     * @covers \Flugg\Responder\TransformationFactory::makeWithModel
     * @covers \Flugg\Responder\TransformationFactory::parseTransformer
     */
    public function makeMethodShouldParseTransformerWhenGivenString()
    {
        // Arrange...
        $data = $this->makeModelWithTransformer(get_class($transformer = $this->makeTransformer()));
        $resourceFactory = $this->mockResourceFactory(new Item($data));
        $transformationFactory = new TransformationFactory($resourceFactory);

        // Act...
        $transformation = $transformationFactory->make($data);

        // Assert...
        $this->assertEquals($transformer, $transformation->getResource()->getTransformer());
    }

    /**
     * Test that you get an [\InvalidArgumentException] when the transformer is invalid.
     *
     * @test
     * @covers \Flugg\Responder\TransformationFactory::make
     * @covers \Flugg\Responder\TransformationFactory::makeWithModel
     * @covers \Flugg\Responder\TransformationFactory::parseTransformer
     * @covers \Flugg\Responder\Exceptions\InvalidTransformerException::__construct
     */
    public function makeMethodShouldThrowExceptionIfTransformerIsInvalid()
    {
        // Arrange...
        $this->expectException(InvalidTransformerException::class);
        $model = $this->makeModel();
        $resourceFactory = $this->mockResourceFactory(new Item($model));
        $transformationFactory = new TransformationFactory($resourceFactory);

        // Act...
        $transformationFactory->make($model, 123);
    }

    /**
     * Test that the [make] method creates a closure transformer from the model.
     *
     * @test
     * @covers \Flugg\Responder\TransformationFactory::make
     * @covers \Flugg\Responder\TransformationFactory::makeWithModel
     * @covers \Flugg\Responder\TransformationFactory::resolveTransformer
     * @covers \Flugg\Responder\TransformationFactory::makeTransformer
     */
    public function makeMethodShouldCreateClosureIfNoTransformerIsFound()
    {
        // Arrange...
        $model = $this->makeModel();
        $resourceFactory = $this->mockResourceFactory(new Item($model));
        $transformationFactory = new TransformationFactory($resourceFactory);

        // Act...
        $transformation = $transformationFactory->make($model);

        // Assert...
        $this->assertInstanceOf(Closure::class, $transformation->getResource()->getTransformer());
    }

    /**
     * Test that a transformer can be set explicitly on the transformation by passing a
     * second argument to the [make] method.
     *
     * @test
     * @covers \Flugg\Responder\TransformationFactory::make
     * @covers \Flugg\Responder\TransformationFactory::makeWithModel
     * @covers \Flugg\Responder\TransformationFactory::parseTransformer
     */
    public function makeMethodShouldAllowSettingTransformerExplicitly()
    {
        // Arrange...
        $transformer = $this->makeTransformer();
        $model = $this->makeModel();
        $resourceFactory = $this->mockResourceFactory(new Item($model));
        $transformationFactory = new TransformationFactory($resourceFactory);

        // Act...
        $transformation = $transformationFactory->make($model, $transformer);

        // Assert...
        $this->assertSame($transformer, $transformation->getResource()->getTransformer());
    }

    /**
     * Test that you can use a class name string as the transformer on the [make] method.
     *
     * @test
     * @covers \Flugg\Responder\TransformationFactory::make
     * @covers \Flugg\Responder\TransformationFactory::makeWithModel
     * @covers \Flugg\Responder\TransformationFactory::parseTransformer
     */
    public function makeMethodShouldAllowSettingTransformerUsingClassName()
    {
        // Arrange...
        $transformer = $this->makeTransformer();
        $model = $this->makeModel();
        $resourceFactory = $this->mockResourceFactory(new Item($model));
        $transformationFactory = new TransformationFactory($resourceFactory);

        // Act...
        $transformation = $transformationFactory->make($model, get_class($transformer));

        // Assert...
        $this->assertEquals($transformer, $transformation->getResource()->getTransformer());
    }

    /**
     * Test that you can set the transformer to a closure.
     *
     * @test
     * @covers \Flugg\Responder\TransformationFactory::make
     * @covers \Flugg\Responder\TransformationFactory::makeWithModel
     * @covers \Flugg\Responder\TransformationFactory::parseTransformer
     */
    public function makeMethodShouldAllowSettingTransformerAsClosure()
    {
        // Arrange...
        $transformer = function () { };
        $model = $this->makeModel();
        $resourceFactory = $this->mockResourceFactory(new Item($model));
        $transformationFactory = new TransformationFactory($resourceFactory);

        // Act...
        $transformation = $transformationFactory->make($model, $transformer);

        // Assert...
        $this->assertSame($transformer, $transformation->getResource()->getTransformer());
    }

    /**
     * Test that the [make] method resolves resource key from the model's table name
     * if not set explicitly.
     *
     * @test
     * @covers \Flugg\Responder\TransformationFactory::make
     * @covers \Flugg\Responder\TransformationFactory::makeWithModel
     * @covers \Flugg\Responder\TransformationFactory::resolveResourceKey
     */
    public function makeMethodShouldResolveResourceKeyFromModelTableName()
    {
        // Arrange...
        $model = $this->makeModel()->setTable('foo');
        $resourceFactory = $this->mockResourceFactory(new Item($model));
        $transformationFactory = new TransformationFactory($resourceFactory);

        // Act...
        $transformation = $transformationFactory->make($model);

        // Assert...
        $this->assertEquals($transformation->getResource()->getResourceKey(), 'foo');
    }

    /**
     * Test that you can override the table name resource key by creating a [getResourceKey]
     * method on the model.
     *
     * @test
     * @covers \Flugg\Responder\TransformationFactory::make
     * @covers \Flugg\Responder\TransformationFactory::makeWithModel
     * @covers \Flugg\Responder\TransformationFactory::resolveResourceKey
     */
    public function makeMethodShouldAllowOverridingResourceKeyWithModelMethod()
    {
        // Arrange...
        $model = $this->makeModelWithResourceKey('bar')->setTable('foo');
        $resourceFactory = $this->mockResourceFactory(new Item($model));
        $transformationFactory = new TransformationFactory($resourceFactory);

        // Act...
        $transformation = $transformationFactory->make($model);

        // Assert...
        $this->assertEquals($transformation->getResource()->getResourceKey(), 'bar');
    }

    /**
     * Test that a resource key can be set explicitly on the transformation by passing a
     * third argument to the [make] method.
     *
     * @test
     * @covers \Flugg\Responder\TransformationFactory::make
     * @covers \Flugg\Responder\TransformationFactory::makeWithModel
     */
    public function makeMethodShouldAllowSettingResourceKeyExplicitly()
    {
        // Arrange...
        $model = $this->makeModel();
        $resourceFactory = $this->mockResourceFactory(new Item($model));
        $transformationFactory = new TransformationFactory($resourceFactory);

        // Act...
        $transformation = $transformationFactory->make($model, null, 'foo');

        // Assert...
        $this->assertEquals('foo', $transformation->getResource()->getResourceKey());
    }

    /**
     * Test that a resource key can be set explicitly on the transformation by passing a
     * third argument to the [make] method.
     *
     * @test
     * @covers \Flugg\Responder\TransformationFactory::make
     * @covers \Flugg\Responder\TransformationFactory::makeWithoutModel
     * @covers \Flugg\Responder\TransformationFactory::makeTransformer
     */
    public function makeMethodShouldAllowArraysWithoutModels()
    {
        // Arrange...
        $resourceFactory = $this->mockResourceFactory(new Collection($data = ['foo' => 'bar']));
        $transformationFactory = new TransformationFactory($resourceFactory);

        // Act...
        $transformation = $transformationFactory->make($data);

        // Assert...
        $this->assertNull($transformation->getModel());
        $this->assertNull($transformation->getResource()->getResourceKey());
        $this->assertInstanceOf(Closure::class, $transformation->getResource()->getTransformer());
    }

    /**
     * Test that a resource key can be set explicitly on the transformation by passing a
     * third argument to the [make] method.
     *
     * @test
     * @covers \Flugg\Responder\TransformationFactory::make
     * @covers \Flugg\Responder\TransformationFactory::makeWithoutModel
     * @covers \Flugg\Responder\TransformationFactory::makeTransformer
     */
    public function makeMethodShouldAllowCollectionsWithoutModels()
    {
        // Arrange...
        $resourceFactory = $this->mockResourceFactory(new Collection($data = collect(['foo' => 'bar'])));
        $transformationFactory = new TransformationFactory($resourceFactory);

        // Act...
        $transformation = $transformationFactory->make($data);

        // Assert...
        $this->assertNull($transformation->getModel());
        $this->assertNull($transformation->getResource()->getResourceKey());
        $this->assertInstanceOf(Closure::class, $transformation->getResource()->getTransformer());
    }

    /**
     * Test that a resource key can be set explicitly on the transformation by passing a
     * third argument to the [make] method.
     *
     * @test
     * @covers \Flugg\Responder\TransformationFactory::make
     * @covers \Flugg\Responder\TransformationFactory::makeWithoutModel
     * @covers \Flugg\Responder\TransformationFactory::makeTransformer
     */
    public function makeMethodShouldAllowSettingTransformerAndResourceKeyWithoutModels()
    {
        // Arrange...
        $transformer = function() {};
        $resourceFactory = $this->mockResourceFactory(new Collection($data = collect(['foo' => 'bar'])));
        $transformationFactory = new TransformationFactory($resourceFactory);

        // Act...
        $transformation = $transformationFactory->make($data, $transformer, 'foo');

        // Assert...
        $this->assertEquals('foo', $transformation->getResource()->getResourceKey());
        $this->assertSame($transformer, $transformation->getResource()->getTransformer());
    }
}