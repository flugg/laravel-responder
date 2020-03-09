<?php

namespace Flugg\Responder\Tests\Unit\Http\Responses;

use Flugg\Responder\Contracts\ErrorSerializer;
use Flugg\Responder\ErrorFactory;
use Flugg\Responder\Exceptions\InvalidErrorSerializerException;
use Flugg\Responder\Http\Responses\ErrorResponseBuilder;
use Flugg\Responder\Serializers\JsonSerializer;
use Flugg\Responder\Tests\TestCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Mockery;
use stdClass;

/**
 * Unit tests for the [Flugg\Responder\Http\Responses\ErrorResponseBuilder] class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class ErrorResponseBuilderTest extends TestCase
{
    /**
     * A mock of a [ResponseFactory] class.
     *
     * @var \Mockery\MockInterface
     */
    protected $responseFactory;

    /**
     * A mock of an [ErrorFactory] class.
     *
     * @var \Mockery\MockInterface
     */
    protected $errorFactory;

    /**
     * A mock of a [SerializerAbstract] class.
     *
     * @var \Mockery\MockInterface
     */
    protected $serializer;

    /**
     * The [ErrorResponseBuilder] class being tested.
     *
     * @var \Flugg\Responder\Http\Responses\ErrorResponseBuilder
     */
    protected $responseBuilder;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->responseFactory = $this->mockResponseFactory();
        $this->errorFactory = Mockery::mock(ErrorFactory::class);
        $this->responseBuilder = new ErrorResponseBuilder($this->responseFactory, $this->errorFactory);
        $this->responseBuilder->serializer($this->serializer = Mockery::mock(ErrorSerializer::class));
    }

    /**
     * Assert that the [respond] generates JSON responses using the [ResponseFactory].
     */
    public function testRespondMethodShouldMakeJsonResponses()
    {
        $response = new JsonResponse($error = ['foo' => 1], $status = 400, $headers = ['x-foo' => 1]);
        $this->errorFactory->shouldReceive('make')->andReturn($error);
        $this->responseFactory->shouldReceive('make')->andReturn($response);

        $result = $this->responseBuilder->respond($status, $headers);

        $this->assertEquals($response, $result);
        $this->responseFactory->shouldHaveReceived('make')->with($error, $status, $headers)->once();
    }

    /**
     * Assert that the [respond] method throws an [InvalidArgumentException] exception if
     * status code is not a valid error code.
     */
    public function testRespondMethodThrowsExceptionIfGivenInvalidStatusCode()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->responseBuilder->respond($status = 200);
    }

    /**
     * Assert that the [toArray] method formats the error output using the [ErrorFactory] and
     * returns the result as an array.
     */
    public function testToArrayMethodShouldFormatErrorUsingErrorFactory()
    {
        $this->errorFactory->shouldReceive('make')->andReturn($error = ['foo' => 1]);

        $result = $this->responseBuilder->toArray();

        $this->assertEquals($error, $result);
    }

    /**
     * Assert that the [toCollection] method formats the error output using the [ErrorFactory]
     * and returns the result as a collection.
     */
    public function testToCollectionMethodShouldFormatErrorAndReturnCollection()
    {
        $this->errorFactory->shouldReceive('make')->andReturn($error = ['foo' => 1]);

        $result = $this->responseBuilder->toCollection();

        $this->assertEquals(new Collection($error), $result);
    }

    /**
     * Assert that the [toJson] method formats the error output using the [ErrorFactory] and
     * returns the result as JSON.
     */
    public function testToJsonMethodShouldFormatErrorAndReturnJson()
    {
        $this->errorFactory->shouldReceive('make')->andReturn($error = ['foo' => 1]);

        $result = $this->responseBuilder->toJson();

        $this->assertEquals(json_encode($error), $result);
    }

    /**
     * Assert that the [toJson] method accepts an argument for setting encoding options.
     */
    public function testToJsonMethodShouldAllowSettingEncodingOptions()
    {
        $this->errorFactory->shouldReceive('make')->andReturn($error = ['foo' => 1]);

        $result = $this->responseBuilder->toJson(JSON_PRETTY_PRINT);

        $this->assertEquals(json_encode($error, JSON_PRETTY_PRINT), $result);
    }

    /**
     * Assert that the [error] method sets the error code and message that is sent to the
     * [ErrorFactory].
     */
    public function testErrorMethodSetsErrorCodeAndMessage()
    {
        $this->errorFactory->shouldReceive('make')->andReturn([]);

        $this->responseBuilder->error($code = 'test_error', $message = 'A test error has occured.')->respond();

        $this->errorFactory->shouldHaveReceived('make')->with($this->serializer, $code, $message, null)->once();
    }

    /**
     * Assert that the [data] method adds error data that is sent to the [ErrorFactory].
     */
    public function testDataMethodSetsErrorData()
    {
        $this->errorFactory->shouldReceive('make')->andReturn([]);

        $this->responseBuilder->data($data = ['foo' => 1])->respond();

        $this->errorFactory->shouldHaveReceived('make')->with($this->serializer, null, null, $data)->once();
    }

    /**
     * Assert that the [serializer] method sets the serializer that is sent to the [ErrorFactory].
     */
    public function testSerializerMethodSetsErrorSerializer()
    {
        $this->errorFactory->shouldReceive('make')->andReturn([]);

        $this->responseBuilder->serializer($serializer = Mockery::mock(ErrorSerializer::class))->respond();

        $this->errorFactory->shouldHaveReceived('make')->with($serializer, null, null, null)->once();
    }

    /**
     * Assert that the [serializer] method allows class name strings.
     */
    public function testSerializerMethodAllowsClassNameStrings()
    {
        $this->errorFactory->shouldReceive('make')->andReturn([]);

        $this->responseBuilder->serializer($serializer = get_class(Mockery::mock(ErrorSerializer::class)))->respond();

        $this->errorFactory->shouldHaveReceived('make')->with($serializer, null, null, null)->once();
    }

    /**
     * Assert that the [serializer] method throws [InvalidErrorSerializerException] exception when
     * given an invalid serializer.
     */
    public function testSerializerMethodThrowsExceptionWhenGivenInvalidSerializer()
    {
        $this->expectException(InvalidErrorSerializerException::class);

        $this->responseBuilder->serializer($serializer = stdClass::class);
    }
}