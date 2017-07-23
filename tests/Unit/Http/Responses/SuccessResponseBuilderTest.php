<?php

namespace Flugg\Responder\Tests\Unit\Http\Responses;

use BadMethodCallException;
use Flugg\Responder\Http\Responses\SuccessResponseBuilder;
use Flugg\Responder\Serializers\JsonSerializer;
use Flugg\Responder\Tests\TestCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Mockery;

/**
 * Unit tests for the [Flugg\Responder\Http\Responses-
 * \SuccessResponseBuilder] class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class SuccessResponseBuilderTest extends TestCase
{
    /**
     * The response factory mock.
     *
     * @var \Mockery\MockInterface
     */
    protected $responseFactory;

    /**
     * The transform builder mock.
     *
     * @var \Mockery\MockInterface
     */
    protected $transformBuilder;

    /**
     * The success response builder.
     *
     * @var \Flugg\Responder\Http\Responses\SuccessResponseBuilder
     */
    protected $responseBuilder;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->responseFactory = $this->mockResponseFactory();
        $this->transformBuilder = $this->mockTransformBuilder();
        $this->responseBuilder = new SuccessResponseBuilder($this->responseFactory, $this->transformBuilder);
    }

    /**
     * Test that the parameters sent to the [transform] method is forwarded to the transform builder.
     */
    public function testTransformMethodShouldMakeResourceWithTheTransformBuilder()
    {
        [$data, $transformer, $resourceKey] = [['foo' => 1], Mockery::mock(BaseTransformer::class), 'foo'];

        $responseBuilder = $this->responseBuilder->transform($data, $transformer, $resourceKey);

        $this->assertSame($responseBuilder, $this->responseBuilder);
        $this->transformBuilder->shouldHaveReceived('resource')->with($data, $transformer, $resourceKey)->once();
    }

    /**
     * Test that the [respond] method calls on the response factory to generate a JSON response.
     */
    public function testRespondMethodShouldMakeAResponseUsingTheResponseFactory()
    {
        [$status, $headers] = [201, ['x-foo' => 1]];
        $this->transformBuilder->shouldReceive('transform')->andReturn($data = ['foo' => 1]);
        $this->responseFactory->shouldReceive('make')
            ->andReturn($response = new JsonResponse($data, $status, $headers));

        $result = $this->responseBuilder->respond($status, $headers);

        $this->assertEquals($response, $result);
        $this->responseFactory->shouldHaveReceived('make')->with($data, $status, $headers)->once();
    }

    /**
     * Test that the [respond] method throws exception if status code is not a valid success code.
     */
    public function testRespondMethodThrowsExceptionIfStatusCodeIsInvalid()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->responseBuilder->respond($status = 400);
    }

    /**
     * Test that the [transform] method runs the transformation using the transform builder and
     * returns the result as an array.
     */
    public function testToArrayMethodShouldTransformDataWithTheTransformBuilderAndReturnArray()
    {
        $this->transformBuilder->shouldReceive('transform')->andReturn($data = ['foo' => 1]);

        $transformation = $this->responseBuilder->toArray();

        $this->assertEquals($data, $transformation);
        $this->transformBuilder->shouldHaveReceived('transform')->withNoArgs()->once();
    }

    /**
     * Test that the [transform] method runs the transformation using the transform builder and
     * returns the result as a collection.
     */
    public function testToCollectionMethodShouldTransformDataWithTheTransformBuilderAndReturnCollection()
    {
        $this->transformBuilder->shouldReceive('transform')->andReturn($data = ['foo' => 1]);

        $transformation = $this->responseBuilder->toCollection();

        $this->assertEquals(new Collection($data), $transformation);
        $this->transformBuilder->shouldHaveReceived('transform')->withNoArgs()->once();
    }

    /**
     * Test that the [toJson] method runs the transformation using the transform builder and
     * returns the result as JSON.
     */
    public function testToJsonMethodShouldTransformDataWithTheTransformBuilderAndReturnJson()
    {
        $this->transformBuilder->shouldReceive('transform')->andReturn($data = ['foo' => 1]);

        $transformation = $this->responseBuilder->toJson();

        $this->assertEquals(json_encode($data), $transformation);
        $this->transformBuilder->shouldHaveReceived('transform')->withNoArgs()->once();
    }

    /**
     * Test that the [toJson] method accepts an argument for setting encoding options.
     */
    public function testToJsonMethodShouldAllowSettingOptions()
    {
        $this->transformBuilder->shouldReceive('transform')->andReturn($data = ['foo' => 1]);

        $transformation = $this->responseBuilder->toJson(JSON_PRETTY_PRINT);

        $this->assertEquals(json_encode($data, JSON_PRETTY_PRINT), $transformation);
    }

    /**
     * Test that the parameters sent to the [meta] method is forwarded to the transform builder.
     */
    public function testMetaMethodShouldAddMetaToTheTransformBuilder()
    {
        $responseBuilder = $this->responseBuilder->meta($meta = ['foo' => 1]);

        $this->assertSame($responseBuilder, $this->responseBuilder);
        $this->transformBuilder->shouldHaveReceived('meta')->with($meta)->once();
    }

    /**
     * Test that the parameters sent to the [serializer] method is forwarded to the transform builder.
     */
    public function testSerializerMethodShouldSetSerializerOnTheTransformBuilder()
    {
        $responseBuilder = $this->responseBuilder->serializer($serializer = JsonSerializer::class);

        $this->assertSame($responseBuilder, $this->responseBuilder);
        $this->transformBuilder->shouldHaveReceived('serializer')->with($serializer)->once();
    }

    /**
     * Test that the parameters sent to the [with] method is forwarded to the transform builder.
     */
    public function testWithMethodShouldAddRelationsToTheTransformBuilder()
    {
        $responseBuilder = $this->responseBuilder->with($relations = ['foo', 'bar']);

        $this->assertSame($responseBuilder, $this->responseBuilder);
        $this->transformBuilder->shouldHaveReceived('with')->with($relations)->once();
    }

    /**
     * Test that the [with] method allows passing of multiple arguments.
     */
    public function testWithMethodShouldAllowMultipleArguments()
    {
        $this->responseBuilder->with(...$relations = ['foo', 'bar']);

        $this->transformBuilder->shouldHaveReceived('with')->with(...$relations)->once();
    }

    /**
     * Test that the parameters sent to the [without] method is forwarded to the transform builder.
     */
    public function testWithoutMethodShouldAddRelationsToTheTransformBuilder()
    {
        $responseBuilder = $this->responseBuilder->without($relations = ['foo', 'bar']);

        $this->assertSame($responseBuilder, $this->responseBuilder);
        $this->transformBuilder->shouldHaveReceived('without')->with($relations)->once();
    }

    /**
     * Test that the [without] method allows passing of multiple arguments.
     */
    public function testWithoutMethodShouldAllowMultipleArguments()
    {
        $this->responseBuilder->without(...$relations = ['foo', 'bar']);

        $this->transformBuilder->shouldHaveReceived('without')->with(...$relations)->once();
    }

    /**
     * Test that the [__call] method should throw a [\BadMethodCallException] if given an
     * unknown method name.
     */
    public function testUnknownMethodsShouldNotBeMagicallyHandled()
    {
        $this->expectException(BadMethodCallException::class);

        $this->responseBuilder->unknownMethod();
    }
}