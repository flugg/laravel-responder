<?php

namespace Flugg\Responder\Tests\Unit;

use Closure;
use Flugg\Responder\Exceptions\InvalidSerializerException;
use Flugg\Responder\Exceptions\SerializerNotFoundException;
use Flugg\Responder\Http\SuccessResponseBuilder;
use Flugg\Responder\Serializers\ApiSerializer;
use Flugg\Responder\Tests\TestCase;
use Flugg\Responder\Transformer;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\NullResource;
use League\Fractal\Resource\ResourceAbstract;
use League\Fractal\Serializer\JsonApiSerializer;

/**
 * Collection of unit tests testing [\Flugg\Responder\Http\SuccessResponseBuilder].
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class SuccessResponseBuilderTest extends TestCase
{
    /**
     * Test that you can resolve an instance of [\League\Fractal\Manager] from the service
     * container.
     *
     * @test
     */
    public function youCanResolveAManagerFromTheContainer()
    {
        // Act...
        $manager = $this->app->make('responder.manager');

        // Assert...
        $this->assertInstanceOf(Manager::class, $manager);
    }

    /**
     * Test that you can resolve an instance of [\Flugg\Responder\SuccessResponseBuilder]
     * from the service container.
     *
     * @test
     */
    public function youCanResolveASuccessResponseBuilderFromTheContainer()
    {
        // Act...
        $responseBuilder = $this->app->make('responder.success');

        // Assert...
        $this->assertInstanceOf(SuccessResponseBuilder::class, $responseBuilder);
    }

    /**
     * Test that you can get an instance of [\League\Fractal\Manager] from the response
     * builder.
     *
     * @test
     */
    public function youCanGetTheManagerInstance()
    {
        // Arrange...
        $responseBuilder = $this->app->make('responder.success');

        // Act...
        $manager = $responseBuilder->getManager();

        // Assert...
        $this->assertInstanceOf(Manager::class, $manager);
    }

    /**
     * Test that a serializer is set to [\Flugg\Responder\Serializers\ApiSerializer] when
     * you leave the configuration to the defaults.
     *
     * @test
     */
    public function itShouldUsePackageSerializerByDefault()
    {
        // Act...
        $responseBuilder = $this->app->make('responder.success');

        // Assert...
        $this->assertInstanceOf(ApiSerializer::class, $responseBuilder->getManager()->getSerializer());
    }

    /**
     * Test that you can change serializer by changing the [serializer] key in the package
     * configuration file.
     *
     * @test
     */
    public function youCanChangeSerializerInTheConfig()
    {
        // Arrange...
        $this->app['config']->set('responder.serializer', JsonApiSerializer::class);

        // Act...
        $responseBuilder = $this->app->make('responder.success');

        // Assert...
        $this->assertInstanceOf(JsonApiSerializer::class, $responseBuilder->getManager()->getSerializer());
    }

    /**
     * Test that you can get an instance of [\League\Fractal\Resource\ResourceAbstract]
     * from the response builder.
     *
     * @test
     */
    public function youCanGetTheResourceInstance()
    {
        // Arrange...
        $responseBuilder = $this->app->make('responder.success');

        // Act...
        $resource = $responseBuilder->getResource();

        // Assert...
        $this->assertInstanceOf(ResourceAbstract::class, $resource);
    }

    /**
     * Test that a new instance of [\League\Fractal\Resource\NullResource] is created when
     * the response builder is instantiated.
     *
     * @test
     */
    public function itShouldCreateANullResourceByDefault()
    {
        // Arrange...
        $resourceFactory = $this->mockResourceFactory(new NullResource);

        // Act...
        $responseBuilder = $this->app->make('responder.success');

        // Assert...
        $this->assertInstanceOf(NullResource::class, $responseBuilder->getResource());
        $resourceFactory->shouldHaveReceived('make')->withNoArgs()->once();
    }

    /**
     * Test that the resource instance is set to [\League\Fractal\Resource\NullResource]
     * when given no data to the [transform] method.
     *
     * @test
     */
    public function transformMethodShouldSetResourceToNullResourceWhenGivenNoData()
    {
        // Arrange...
        $resourceFactory = $this->mockResourceFactory(new NullResource);
        $responseBuilder = $this->app->make('responder.success');

        // Act...
        $responseBuilder->transform();

        // Assert...
        $this->assertInstanceOf(NullResource::class, $responseBuilder->getResource());
        $resourceFactory->shouldHaveReceived('make')->with(null)->once();
    }

    /**
     * Test that the Fractal resource instance on the response is updated when calling the
     * [transform] method using the resource factory.
     *
     * @test
     */
    public function transformMethodShouldResolveResourceFromData()
    {
        // Arrange...
        $resourceFactory = $this->mockResourceFactory(new Item);
        $responseBuilder = $this->app->make('responder.success');
        $model = $this->makeModel();

        // Act...
        $responseBuilder->transform($model);

        // Assert...
        $this->assertInstanceOf(Item::class, $responseBuilder->getResource());
        $resourceFactory->shouldHaveReceived('make')->with($model)->once();
    }

    /**
     * Test that the [transform] method throws an [\InvalidArgumentException] when the given
     * data doesn't contain, or is itself, an Eloquent model.
     *
     * @test
     */
    public function transformMethodShouldFailIfNoModelIsFound()
    {
        // Arrange...
        $responseBuilder = $this->app->make('responder.success');
        $this->expectException(InvalidArgumentException::class);

        // Act...
        $responseBuilder->transform('foo');
    }

    /**
     * Test that the [transform] method resolves a transformer from the model resolved from
     * the given data.
     *
     * @test
     */
    public function transformMethodShouldResolveTransformerFromModel()
    {
        // Arrange...
        $responseBuilder = $this->app->make('responder.success');
        $model = $this->makeModelWithTransformer($this->makeTransformer());

        // Act...
        $responseBuilder->transform($model);

        // Assert...
        $this->assertInstanceOf(Transformer::class, $responseBuilder->getResource()->getTransformer());
    }

    /**
     * Test that the [transform] method can resolve a transformer from the model, when the
     * [transformer] method returns a class name string.
     *
     * @test
     */
    public function transformMethodShouldResolveTransformerUsingClassName()
    {
        // Arrange...
        $responseBuilder = $this->app->make('responder.success');
        $model = $this->makeModelWithTransformer(get_class($this->makeTransformer()));

        // Act...
        $responseBuilder->transform($model);

        // Assert...
        $this->assertInstanceOf(Transformer::class, $responseBuilder->getResource()->getTransformer());
    }

    /**
     * Test that the [transform] method creates a closure transformer substitute from the
     * model's fillable array.
     *
     * @test
     */
    public function transformMethodShouldCreateClosureIfNoTransformerIsFound()
    {
        // Arrange...
        $responseBuilder = $this->app->make('responder.success');
        $model = $this->makeModel();

        // Act...
        $responseBuilder->transform($model);

        // Assert...
        $this->assertInstanceOf(Closure::class, $responseBuilder->getResource()->getTransformer());
    }

    /**
     * Test that a transformer can be set explicitly on the response by passing a second
     * argument to the [transform] method.
     *
     * @test
     */
    public function transformMethodShouldAllowSettingATransformer()
    {
        // Arrange...
        $responseBuilder = $this->app->make('responder.success');
        $model = $this->makeModel();
        $transformer = $this->makeTransformer();

        // Act...
        $responseBuilder->transform($model, $transformer);

        // Assert...
        $this->assertSame($transformer, $responseBuilder->getResource()->getTransformer());
    }

    /**
     * Test that you can use a class name string to set the transformer on the [transform]
     * method.
     *
     * @test
     */
    public function transformMethodShouldAllowSettingATransformerUsingClassName()
    {
        // Arrange...
        $responseBuilder = $this->app->make('responder.success');
        $model = $this->makeModel();
        $transformer = get_class($this->makeTransformer());

        // Act...
        $responseBuilder->transform($model, $transformer);

        // Assert...
        $this->assertInstanceOf($transformer, $responseBuilder->getResource()->getTransformer());
    }

    /**
     * Test that you can also use an anonymous function as a transformer instead of a full
     * blown transformer class when using the [transform] method.
     *
     * @test
     */
    public function transformMethodShouldAllowSettingATransformerUsingClosure()
    {
        // Arrange...
        $responseBuilder = $this->app->make('responder.success');
        $model = $this->makeModel();
        $transformer = function () { };

        // Act...
        $responseBuilder->transform($model, $transformer);

        // Assert...
        $this->assertSame($transformer, $responseBuilder->getResource()->getTransformer());
    }

    /**
     * Test that the [transform] method sets the eager loaded relations from the model on
     * the transformer and manager.
     *
     * @test
     */
    public function transformMethodShouldSetRelations()
    {
        // Arrange...
        $responseBuilder = $this->app->make('responder.success');
        $model = $this->makeModel()->setRelation('foo', null);
        $transformer = $this->makeTransformer();

        // Act...
        $responseBuilder->transform($model, $transformer);

        // Assert...
        $this->assertEquals(['foo'], $responseBuilder->getResource()->getTransformer()->getRelations());
        $this->assertEquals(['foo'], $responseBuilder->getManager()->getRequestedIncludes());
    }

    /**
     * Test that the [transform] method merges the eager loaded relations with relations
     * set directly on the transformer.
     *
     * @test
     */
    public function transformMethodShouldMergeRelationsWithExisting()
    {
        // Arrange...
        $responseBuilder = $this->app->make('responder.success');
        $model = $this->makeModel()->setRelation('foo', null);
        $transformer = $this->makeTransformer()->setRelations('bar');

        // Act...
        $responseBuilder->transform($model, $transformer);

        // Assert...
        $this->assertEquals(['bar', 'foo'], $responseBuilder->getResource()->getTransformer()->getRelations());
    }

    /**
     * Test that the [transform] method resolves resource key from the model's table name
     * if not set explicitly.
     *
     * @test
     */
    public function transformMethodShouldResolveResourceKeyFromTableNameByDefault()
    {
        // Arrange...
        $responseBuilder = $this->app->make('responder.success');
        $model = $this->makeModel()->setTable('foo');

        // Act...
        $responseBuilder->transform($model);

        // Assert...
        $this->assertEquals($responseBuilder->getResource()->getResourceKey(), 'foo');
    }

    /**
     * Test that you can set a resource key directly on your models by taking use of the
     * [getResourceKey] method.
     *
     * @test
     */
    public function transformMethodShouldGetResourceKeyFromModelMethod()
    {
        // Arrange...
        $responseBuilder = $this->app->make('responder.success');
        $model = $this->makeModelWithResourceKey('foo');

        // Act...
        $responseBuilder->transform($model);

        // Assert...
        $this->assertEquals($responseBuilder->getResource()->getResourceKey(), 'foo');
    }

    /**
     * Test that a resource key can be set explicitly on the response by passing a third
     * argument to the [transform] method.
     *
     * @test
     */
    public function transformMethodShouldAllowSettingAResourceKey()
    {
        // Arrange...
        $responseBuilder = $this->app->make('responder.success');
        $model = $this->makeModel();
        $transformer = function () { };

        // Act...
        $responseBuilder->transform($model, $transformer, 'foo');

        // Assert...
        $this->assertEquals($responseBuilder->getResource()->getResourceKey(), 'foo');
    }

    /**
     * Test that the [transform] method returns the response builder, allowing for fluent
     * method chaining.
     *
     * @test
     */
    public function transformMethodShouldReturnItself()
    {
        // Arrange...
        $responseBuilder = $this->app->make('responder.success');

        // Act...
        $result = $responseBuilder->transform();

        // Assert...
        $this->assertSame($responseBuilder, $result);
    }

    /**
     * Test that the [addMeta] method adds the meta data to the resource instance.
     *
     * @test
     */
    public function addMetaMethodShouldAddMetaDataToResource()
    {
        // Arrange...
        $responseBuilder = $this->app->make('responder.success');
        $meta = ['foo' => 1];

        // Act...
        $responseBuilder->addMeta($meta);

        // Assert...
        $this->assertEquals($responseBuilder->getResource()->getMeta(), $meta);
    }

    /**
     * Test that the [addMeta] method merges new meta data with existing meta data
     * set on the resource.
     *
     * @test
     */
    public function addMetaMethodShouldMergeMetaDataWithExisting()
    {
        // Arrange...
        $responseBuilder = $this->app->make('responder.success');
        $meta = ['foo' => 1];
        $moreMeta = ['bar' => 2];

        // Act...
        $responseBuilder->addMeta($meta)->addMeta($moreMeta);

        // Assert...
        $this->assertEquals($responseBuilder->getResource()->getMeta(), array_merge($meta, $moreMeta));
    }

    /**
     * Test that the [addMeta] method returns the response builder, allowing for fluent
     * method chaining.
     *
     * @test
     */
    public function addMetaMethodShouldReturnItself()
    {
        // Arrange...
        $responseBuilder = $this->app->make('responder.success');

        // Act...
        $result = $responseBuilder->addMeta([]);

        // Assert...
        $this->assertSame($responseBuilder, $result);
    }

    /**
     * Test that the [serializer] method sets the serializer given on the Fractal manager.
     *
     * @test
     */
    public function serializerMethodShouldSetSerializerOnTheManager()
    {
        // Arrange...
        $responseBuilder = $this->app->make('responder.success');

        // Act...
        $responseBuilder->serializer(new JsonApiSerializer);

        // Assert...
        $this->assertInstanceOf(JsonApiSerializer::class, $responseBuilder->getManager()->getSerializer());
    }

    /**
     * Test that the [serializer] method allows for setting serializer using a string.
     *
     * @test
     */
    public function serializerMethodShouldResolveSerializerFromClassName()
    {
        // Arrange...
        $responseBuilder = $this->app->make('responder.success');

        // Act...
        $responseBuilder->serializer(JsonApiSerializer::class);

        // Assert...
        $this->assertInstanceOf(JsonApiSerializer::class, $responseBuilder->getManager()->getSerializer());
    }

    /**
     * Test that the [serializer] method throws an [\InvalidArgumentException] when the given
     * serializer is not a valid value.
     *
     * @test
     */
    public function serializerMethodShouldFailIfSerializerIsInvalid()
    {
        // Arrange...
        $responseBuilder = $this->app->make('responder.success');
        $this->expectException(InvalidSerializerException::class);

        // Act...
        $responseBuilder->serializer(123);
    }

    /**
     * Test that the [serializer] method returns the response builder, allowing for fluent
     * method chaining.
     *
     * @test
     */
    public function serializerMethodShouldReturnItself()
    {
        // Arrange...
        $responseBuilder = $this->app->make('responder.success');

        // Act...
        $result = $responseBuilder->serializer(new JsonApiSerializer);

        // Assert...
        $this->assertSame($responseBuilder, $result);
    }

    /**
     * Test that the [respond] method converts the success response into an instance of
     * [\Illuminate\Http\JsonResponse] with a default status code of 200.
     *
     * @test
     */
    public function respondMethodShouldReturnAJsonResponse()
    {
        // Arrange...
        $responseBuilder = $this->app->make('responder.success');

        // Act...
        $response = $responseBuilder->respond();
        $responseArray = json_decode($response->content(), true);

        // Assert...
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals($response->status(), 200);
        $this->assertArrayHasKey('success', $responseArray);
        $this->assertEquals(true, $responseArray['success']);
    }

    /**
     * Test that the [respond] method does not respond with success flag
     *
     * @test
     */
    public function respondMethodShouldNotOutputSuccessFlagWhenDisabled()
    {
        $this->app['config']->set('responder.include_success_flag', false);
        $responseBuilder = $this->app->make('responder.success');

        $response = $responseBuilder->respond();
        $responseArray = json_decode($response->content(), true);

        $this->assertArrayNotHasKey('success', $responseArray);
    }

    /**
     * Test that the [respond] method allows passing a status code as the first parameter.
     *
     * @test
     */
    public function respondMethodShouldAllowSettingStatusCode()
    {
        // Arrange...
        $responseBuilder = $this->app->make('responder.success');

        // Act...
        $response = $responseBuilder->respond(201);

        // Assert...
        $this->assertEquals($response->status(), 201);
    }

    /**
     * Test that you can set any headers to the JSON response by passing a second argument
     * to the [respond] method.
     *
     * @test
     */
    public function respondMethodShouldAllowSettingHeaders()
    {
        // Arrange...
        $responseBuilder = $this->app->make('responder.success');

        // Act...
        $response = $responseBuilder->respond(201, [
            'x-foo' => true
        ]);

        // Assert...
        $this->assertArrayHasKey('x-foo', $response->headers->all());
    }

    /**
     * Test that the [setStatus] method sets the HTTP status code on the response, providing
     * an alternative, more explicit way of setting the status code.
     *
     * @test
     */
    public function setStatusMethodShouldSetStatusCode()
    {
        // Arrange...
        $responseBuilder = $this->app->make('responder.success');

        // Act...
        $responseBuilder->setStatus(201);

        // Assert...
        $this->assertEquals($responseBuilder->respond()->status(), 201);
    }

    /**
     * Test that the [setStatus] method throws an [\InvalidArgumentException] when the status
     * code given is not a valid successful HTTP status code.
     *
     * @test
     */
    public function setStatusMethodShouldFailIfStatusCodeIsInvalid()
    {
        // Arrange...
        $responseBuilder = $this->app->make('responder.success');
        $this->expectException(InvalidArgumentException::class);

        // Act...
        $responseBuilder->setStatus(400);
    }

    /**
     * Test that the [setStatus] method returns the response builder, allowing for fluent
     * method chaining.
     *
     * @test
     */
    public function setStatusMethodShouldReturnItself()
    {
        // Arrange...
        $responseBuilder = $this->app->make('responder.success');

        // Act...
        $result = $responseBuilder->setStatus(201);

        // Assert...
        $this->assertSame($responseBuilder, $result);
    }

    /**
     * Test that the [toArray] method serializes the data given, using the default serializer
     * and no data.
     *
     * @test
     */
    public function toArrayMethodShouldSerializeData()
    {
        // Arrange...
        $responseBuilder = $this->app->make('responder.success');

        // Act...
        $array = $responseBuilder->toArray();

        // Assert...
        $this->assertEquals([
            'data' => null
        ], $array);
    }

    /**
     * Test that the [toArray] method also transforms the data using the set transformer.
     *
     * @test
     */
    public function toArrayMethodShouldTransformData()
    {
        // Arrange...
        $responseBuilder = $this->app->make('responder.success');
        $model = $this->makeModel(['foo' => 123]);
        $responseBuilder->transform($model, function ($model) {
            return ['foo' => (string) $model->foo];
        });

        // Act...
        $array = $responseBuilder->toArray();

        // Assert...
        $this->assertContains(['foo' => '123'], $array);
    }

    /**
     * Test that the [toCollection] serializes the data into a collection.
     *
     * @test
     */
    public function toCollectionMethodShouldReturnACollection()
    {
        // Arrange...
        $responseBuilder = $this->app->make('responder.success');

        // Act...
        $collection = $responseBuilder->toCollection();

        // Assert...
        $this->assertEquals(collect([
            'data' => null
        ]), $collection);
    }

    /**
     * Test that the [toJson] serializes the data into a JSON string.
     *
     * @test
     */
    public function toJsonMethodShouldReturnJson()
    {
        // Arrange...
        $responseBuilder = $this->app->make('responder.success');

        // Act...
        $json = $responseBuilder->toCollection();

        // Assert...
        $this->assertEquals(json_encode([
            'data' => null
        ]), $json);
    }
}