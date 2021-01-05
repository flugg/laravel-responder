<?php

namespace Flugg\Responder\Tests\Unit\Http\Builders;

use Flugg\Responder\Contracts\Http\Formatter;
use Flugg\Responder\Contracts\Http\ResponseFactory;
use Flugg\Responder\Http\Builders\SuccessResponseBuilder;
use Flugg\Responder\Tests\IncreaseStatusByOneDecorator;
use Flugg\Responder\Tests\UnitTestCase;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Unit tests for the [Flugg\Responder\Http\Builders\SuccessResponseBuilder] class.
 *
 * @see \Flugg\Responder\Http\Builders\SuccessResponseBuilder
 */
class SuccessResponseBuilderTest extends UnitTestCase
{
    /**
     * Mock of a response factory.
     *
     * @var \Flugg\Responder\Contracts\Http\ResponseFactory
     */
    protected $responseFactory;

    /**
     * Mock of a service container.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * Mock of a config repository.
     *
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    /**
     * Class being tested.
     *
     * @var \Flugg\Responder\Http\Builders\SuccessResponseBuilder
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

        $this->responseFactory = mock(ResponseFactory::class);
        $this->container = mock(Container::class);
        $this->config = mock(Repository::class);
        $this->responseBuilder = new SuccessResponseBuilder($this->responseFactory, $this->container, $this->config);
    }

    /**
     * Assert that the [respond] method generate a response using [ResponseFactory].
     */
    public function testRespondMethodShouldMakeResponse()
    {
        $this->responseFactory->allows(['make' => $response = mock(JsonResponse::class)]);

        $result = $this->responseBuilder->make()->respond($status = 300, $headers = ['x-foo' => 1]);

        $this->assertEquals($response, $result);
        $this->responseFactory->shouldHaveReceived('make')->with(null, $status, $headers)->once();
    }

    /**
     * Assert that the [respond] method defaults to a status code of 200.
     */
    public function testRespondMethodDefaultsToStatusCode200()
    {
        $this->responseFactory->allows(['make' => $response = mock(JsonResponse::class)]);

        $result = $this->responseBuilder->make()->respond();

        $this->assertEquals($response, $result);
        $this->responseFactory->shouldHaveReceived('make')->with(null, 200, null)->once();
    }

    /**
     * Assert that the [toResponse] method is an alternative of the [respond] method.
     */
    public function testToResponseMethodShouldMakeResponse()
    {
        $this->responseFactory->allows(['make' => $response = mock(JsonResponse::class)]);

        $result = $this->responseBuilder->make()->toResponse(mock(Request::class));

        $this->assertEquals($response, $result);
        $this->responseFactory->shouldHaveReceived('make')->with(null, 200, null)->once();
    }

    /**
     * Assert that the [toArray] method returns response data as an array.
     */
    public function testToArrayMethodReturnsResponseAsArray()
    {
        $this->responseFactory->allows(['make' => $response = mock(JsonResponse::class)]);
        $response->allows(['getData' => $data = ['foo' => 1]]);

        $result = $this->responseBuilder->make()->toArray();

        $this->assertEquals($data, $result);
    }

    /**
     * Assert that the [toCollection] method returns response data as an Illuminate collection.
     */
    public function testToArrayMethodReturnsResponseAsCollection()
    {
        $this->responseFactory->allows(['make' => $response = mock(JsonResponse::class)]);
        $response->allows(['getData' => $data = ['foo' => 1]]);

        $result = $this->responseBuilder->make()->toCollection();

        $this->assertEquals(Collection::make($data), $result);
    }

    /**
     * Assert that the [toJson] method returns response data as a JSON string.
     */
    public function testToJsonMethodReturnsResponseAsJson()
    {
        $this->responseFactory->allows(['make' => $response = mock(JsonResponse::class)]);
        $response->allows(['getData' => $data = ['foo' => 1]]);

        $result = $this->responseBuilder->make()->toJson(JSON_PRETTY_PRINT);

        $this->assertEquals(json_encode($data, JSON_PRETTY_PRINT), $result);
    }

    /**
     * Assert that the [JsonSerialize] method returns response data as an array.
     */
    public function testJsonSerializeMethodReturnsResponseAsArray()
    {
        $this->responseFactory->allows(['make' => $response = mock(JsonResponse::class)]);
        $response->allows(['getData' => $data = ['foo' => 1]]);

        $result = $this->responseBuilder->make()->toArray();

        $this->assertEquals($data, $result);
    }

    /**
     * Assert that the [formatter] method sets a response formatter used to format the response data.
     */
    public function testFormatterMethodSetsFormatter()
    {
        $formatter = mock(Formatter::class);
        $formatter->allows(['success' => $data = ['foo' => 1]]);
        $this->responseFactory->allows(['make' => mock(JsonResponse::class)]);

        $this->responseBuilder->make()->formatter($formatter)->respond();

        $formatter->shouldHaveReceived('success')->once();
        $this->responseFactory->shouldHaveReceived('make')->with($data, 200, null)->once();
    }

    /**
     * Assert that the [formatter] method resolves a formatter from the service container when given a string.
     */
    public function testFormatterMethodResolvesFormatterFromContainer()
    {
        $this->container->allows(['make' => $formatter = mock(Formatter::class)]);
        $formatter->allows(['success' => $data = ['foo' => 1]]);
        $this->responseFactory->allows(['make' => mock(JsonResponse::class)]);

        $this->responseBuilder->make()->formatter('foo')->respond();

        $formatter->shouldHaveReceived('success')->once();
        $this->container->shouldHaveReceived('make')->with('foo')->once();
        $this->responseFactory->shouldHaveReceived('make')->with($data, 200, null)->once();
    }

    /**
     * Assert that the [decorate] method decorates the [ResponseFactory].
     */
    public function testDecorateMethodDecoratesResponseFactory()
    {
        $this->responseFactory->allows(['make' => mock(JsonResponse::class)]);

        $this->responseBuilder->make()->decorate(IncreaseStatusByOneDecorator::class)->respond();

        $this->responseFactory->shouldHaveReceived('make')->with(null, 201, null)->once();
    }

    /**
     * Assert that the [decorate] method accepts multiple decorators.
     */
    public function testDecorateMethodAcceptsMultipleDecorators()
    {
        $this->responseFactory->allows(['make' => mock(JsonResponse::class)]);

        $this->responseBuilder->make()->decorate([
            IncreaseStatusByOneDecorator::class,
            IncreaseStatusByOneDecorator::class,
            IncreaseStatusByOneDecorator::class
        ])->respond();

        $this->responseFactory->shouldHaveReceived('make')->with(null, 203, null)->once();
    }

    /**
     * Assert that the [meta] method sets metadata attached to the response.
     */
    public function testMetaMethodSetsMetadata()
    {
        $formatter = mock(Formatter::class);
        $formatter->allows('success')->andReturnUsing(function ($response) {
            return ['meta' => $response->meta()];
        });
        $this->responseFactory->allows(['make' => mock(JsonResponse::class)]);

        $this->responseBuilder->make()->meta($meta = ['foo' => 1])->formatter($formatter)->respond();

        $this->responseFactory->shouldHaveReceived('make')->with(['meta' => $meta], 200, null)->once();
    }

    /**
     * Assert that [make] sets data on response.
     */
    public function testMakeMethodShouldSetData()
    {
        $formatter = mock(Formatter::class);
        $formatter->allows('success')->andReturnUsing(function ($response) {
            return ['data' => $response->resource()->data()];
        });
        $this->responseFactory->allows(['make' => mock(JsonResponse::class)]);

        $this->responseBuilder->make($data = ['foo' => 1])->formatter($formatter)->respond();
        $this->responseFactory->shouldHaveReceived('make')->with(['data' => $data], 200, null)->once();
    }
}
