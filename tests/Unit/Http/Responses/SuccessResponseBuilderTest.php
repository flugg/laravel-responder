<?php

namespace Flugg\Responder\Tests\Unit\Http\Responses;

use BadMethodCallException;
use Flugg\Responder\Http\Responses\SuccessResponseBuilder;
use Flugg\Responder\Serializers\JsonSerializer;
use Flugg\Responder\Tests\TestCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * Unit tests for the [Flugg\Responder\Http\Responses\SuccessResponseBuilder] class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
final class SuccessResponseBuilderTest extends TestCase
{
    /**
     * A mock of a [ResponseFactory] class.
     *
     * @var \Mockery\MockInterface
     */
    protected $responseFactory;

    /**
     * A mock of a [TransformBuilder] class.
     *
     * @var \Mockery\MockInterface
     */
    protected $transformBuilder;

    /**
     * The [SuccessResponseBuilder] class being tested.
     *
     * @var \Flugg\Responder\Http\Responses\SuccessResponseBuilder
     */
    protected $responseBuilder;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->responseFactory = $this->mockResponseFactory();
        $this->transformBuilder = $this->mockTransformBuilder();
        $this->responseBuilder = new SuccessResponseBuilder($this->responseFactory, $this->transformBuilder);
    }

    /**
     * Assert that the parameters sent to the [transform] method is forwarded to the
     * [TransformBuilder].
     */
    public function testTransformMethodShouldMakeResources(): void
    {
        $builder = $this->responseBuilder->transform($data = ['foo' => 1], $transformer = $this->mockTransformer(), $key = 'foo');

        $this->assertSame($builder, $this->responseBuilder);
        $this->transformBuilder->shouldHaveReceived('resource')->with($data, $transformer, $key)->once();
    }

    /**
     * Assert that the [respond] generates JSON responses using the [ResponseFactory].
     */
    public function testRespondMethodShouldMakeJsonResponses(): void
    {
        $response = new JsonResponse($data = ['foo' => 1], $status = 201, $headers = ['x-foo' => 1]);
        $this->transformBuilder->shouldReceive('transform')->andReturn($data);
        $this->responseFactory->shouldReceive('make')->andReturn($response);

        $result = $this->responseBuilder->respond($status, $headers);

        $this->assertEquals($response, $result);
        $this->responseFactory->shouldHaveReceived('make')->with($data, $status, $headers)->once();
    }

    /**
     * Assert that the [respond] method throws an [InvalidArgumentException] exception if
     * status code is not a valid success code.
     */
    public function testRespondMethodThrowsExceptionIfGivenInvalidStatusCode(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->responseBuilder->respond($status = 400);
    }

    /**
     * Assert that the [toArray] method formats the success output using the [TransformBuilder]
     * and returns the result as an array.
     */
    public function testToArrayMethodShouldFormatErrorUsingErrorFactory(): void
    {
        $this->transformBuilder->shouldReceive('transform')->andReturn($data = ['foo' => 1]);

        $transformation = $this->responseBuilder->toArray();

        $this->assertEquals($data, $transformation);
        $this->transformBuilder->shouldHaveReceived('transform')->withNoArgs()->once();
    }

    /**
     * Assert that the [toCollection] method formats the success output using the [TransformBuilder]
     * and returns the result as a collection.
     */
    public function testToCollectionMethodShouldFormatErrorAndReturnCollection(): void
    {
        $this->transformBuilder->shouldReceive('transform')->andReturn($data = ['foo' => 1]);

        $transformation = $this->responseBuilder->toCollection();

        $this->assertEquals(new Collection($data), $transformation);
        $this->transformBuilder->shouldHaveReceived('transform')->withNoArgs()->once();
    }

    /**
     * Assert that the [toJson] method formats the success output using the [TransformBuilder] and
     * returns the result as JSON.
     */
    public function testToJsonMethodShouldFormatErrorAndReturnJson(): void
    {
        $this->transformBuilder->shouldReceive('transform')->andReturn($data = ['foo' => 1]);

        $transformation = $this->responseBuilder->toJson();

        $this->assertEquals(json_encode($data), $transformation);
        $this->transformBuilder->shouldHaveReceived('transform')->withNoArgs()->once();
    }

    /**
     * Assert that the [toJson] method accepts an argument for setting encoding options.
     */
    public function testToJsonMethodShouldAllowSettingEncodingOptions(): void
    {
        $this->transformBuilder->shouldReceive('transform')->andReturn($data = ['foo' => 1]);

        $transformation = $this->responseBuilder->toJson(JSON_PRETTY_PRINT);

        $this->assertEquals(json_encode($data, JSON_PRETTY_PRINT), $transformation);
    }

    /**
     * Assert that the data sent to the [meta] method is forwarded to the [TransformBuilder].
     */
    public function testMetaMethodShouldAddMetaToTransformBuilder(): void
    {
        $responseBuilder = $this->responseBuilder->meta($meta = ['foo' => 1]);

        $this->assertSame($responseBuilder, $this->responseBuilder);
        $this->transformBuilder->shouldHaveReceived('meta')->with($meta)->once();
    }

    /**
     * Assert that the serializer sent to the [serializer] method is forwarded to the [TransformBuilder].
     */
    public function testSerializerMethodShouldSetSerializerOnTransformBuilder(): void
    {
        $responseBuilder = $this->responseBuilder->serializer($serializer = JsonSerializer::class);

        $this->assertSame($responseBuilder, $this->responseBuilder);
        $this->transformBuilder->shouldHaveReceived('serializer')->with($serializer)->once();
    }

    /**
     * Assert that the relations sent to the [with] method is forwarded to the [TransformBuilder].
     */
    public function testWithMethodShouldAddRelationsToTransformBuilder(): void
    {
        $responseBuilder = $this->responseBuilder->with($relations = ['foo', 'bar']);

        $this->assertSame($responseBuilder, $this->responseBuilder);
        $this->transformBuilder->shouldHaveReceived('with')->with($relations)->once();
    }

    /**
     * Assert that the [with] method allows passing multiple strings instead of an array.
     */
    public function testWithMethodShouldAllowMultipleStringArguments(): void
    {
        $this->responseBuilder->with(...$relations = ['foo', 'bar']);

        $this->transformBuilder->shouldHaveReceived('with')->with(...$relations)->once();
    }

    /**
     * Assert that the relations sent to the [without] method is forwarded to the [TransformBuilder].
     */
    public function testWithoutMethodShouldAddRelationsToTheTransformBuilder(): void
    {
        $responseBuilder = $this->responseBuilder->without($relations = ['foo', 'bar']);

        $this->assertSame($responseBuilder, $this->responseBuilder);
        $this->transformBuilder->shouldHaveReceived('without')->with($relations)->once();
    }

    /**
     * Assert that the [without] method allows passing multiple strings instead of an array.
     */
    public function testWithoutMethodShouldAllowMultipleStringArguments(): void
    {
        $this->responseBuilder->without(...$relations = ['foo', 'bar']);

        $this->transformBuilder->shouldHaveReceived('without')->with(...$relations)->once();
    }

    /**
     * Assert that the [__call] method should throw a [BadMethodCallException] exception if
     * given an unknown method name.
     */
    public function testUnknownMethodsShouldThrowException(): void
    {
        $this->expectException(BadMethodCallException::class);

        $this->responseBuilder->unknownMethod();
    }
}