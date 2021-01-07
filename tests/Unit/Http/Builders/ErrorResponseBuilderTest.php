<?php

namespace Flugg\Responder\Tests\Unit\Http\Builders;

use Flugg\Responder\Contracts\ErrorMessageRegistry;
use Flugg\Responder\Contracts\Http\Formatter;
use Flugg\Responder\Contracts\Http\ResponseFactory;
use Flugg\Responder\Http\Builders\ErrorResponseBuilder;
use Flugg\Responder\Tests\IncreaseStatusByOneDecorator;
use Flugg\Responder\Tests\UnitTestCase;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * Unit tests for the [Flugg\Responder\Http\Builders\ErrorResponseBuilder] class.
 *
 * @see \Flugg\Responder\Http\Builders\ErrorResponseBuilder
 */
class ErrorResponseBuilderTest extends UnitTestCase
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
     * Mock of an error message registry.
     *
     * @var Flugg\Responder\Contracts\ErrorMessageRegistry
     */
    protected $messageRegistry;

    /**
     * Class being tested.
     *
     * @var \Flugg\Responder\Http\Builders\ErrorResponseBuilder
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
        $this->messageRegistry = mock(ErrorMessageRegistry::class);
        $this->responseBuilder = new ErrorResponseBuilder($this->responseFactory, $this->container, $this->config, $this->messageRegistry);
    }

    /**
     * Assert that the [respond] method generate a response using [ResponseFactory].
     */
    public function testRespondMethodShouldMakeResponse()
    {
        $this->responseFactory->allows(['make' => $response = mock(JsonResponse::class)]);

        $result = $this->responseBuilder->make()->respond($status = 400, $headers = ['x-foo' => 1]);

        $this->assertEquals($response, $result);
        $this->responseFactory->shouldHaveReceived('make')->with(['message' => null], $status, $headers)->once();
    }

    /**
     * Assert that the [respond] method defaults to a status code of 500.
     */
    public function testRespondMethodDefaultsToStatusCode500()
    {
        $this->responseFactory->allows(['make' => $response = mock(JsonResponse::class)]);

        $result = $this->responseBuilder->make()->respond();

        $this->assertEquals($response, $result);
        $this->responseFactory->shouldHaveReceived('make')->with(['message' => null], 500, null)->once();
    }

    /**
     * Assert that the [toResponse] method is an alternative of the [respond] method.
     */
    public function testToResponseMethodShouldMakeResponse()
    {
        $this->responseFactory->allows(['make' => $response = mock(JsonResponse::class)]);

        $result = $this->responseBuilder->make()->toResponse(mock(Request::class));

        $this->assertEquals($response, $result);
        $this->responseFactory->shouldHaveReceived('make')->with(['message' => null], 500, null)->once();
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
        $formatter->allows(['error' => $data = ['foo' => 1]]);
        $this->responseFactory->allows(['make' => mock(JsonResponse::class)]);

        $this->responseBuilder->make()->formatter($formatter)->respond();

        $formatter->shouldHaveReceived('error')->once();
        $this->responseFactory->shouldHaveReceived('make')->with($data, 500, null)->once();
    }

    /**
     * Assert that the [formatter] method resolves a formatter from the service container when given a string.
     */
    public function testFormatterMethodResolvesFormatterFromContainer()
    {
        $this->container->allows(['make' => $formatter = mock(Formatter::class)]);
        $formatter->allows(['error' => $data = ['foo' => 1]]);
        $this->responseFactory->allows(['make' => mock(JsonResponse::class)]);

        $this->responseBuilder->make()->formatter('foo')->respond();

        $formatter->shouldHaveReceived('error')->once();
        $this->container->shouldHaveReceived('make')->with('foo')->once();
        $this->responseFactory->shouldHaveReceived('make')->with($data, 500, null)->once();
    }

    /**
     * Assert that the [decorate] method decorates the [ResponseFactory].
     */
    public function testDecorateMethodDecoratesResponseFactory()
    {
        $this->responseFactory->allows(['make' => mock(JsonResponse::class)]);

        $this->responseBuilder->make()->decorate(IncreaseStatusByOneDecorator::class)->respond();

        $this->responseFactory->shouldHaveReceived('make')->with(['message' => null], 501, null)->once();
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
            IncreaseStatusByOneDecorator::class,
        ])->respond();

        $this->responseFactory->shouldHaveReceived('make')->with(['message' => null], 503, null)->once();
    }

    /**
     * Assert that the [meta] method sets metadata attached to the response.
     */
    public function testMetaMethodSetsMetadata()
    {
        $formatter = mock(Formatter::class);
        $formatter->allows('error')->andReturnUsing(function ($response) {
            return ['meta' => $response->meta()];
        });
        $this->responseFactory->allows(['make' => mock(JsonResponse::class)]);

        $this->responseBuilder->make()->meta($meta = ['foo' => 1])->formatter($formatter)->respond();

        $this->responseFactory->shouldHaveReceived('make')->with(['meta' => $meta], 500, null)->once();
    }

    /**
     * Assert that [make] sets error code and message on response.
     */
    public function testMakeMethodShouldSetErrorCodeAndMessage()
    {
        $formatter = mock(Formatter::class);
        $formatter->allows('error')->andReturnUsing(function ($response) {
            return ['code' => $response->code(), 'message' => $response->message()];
        });
        $this->responseFactory->allows(['make' => mock(JsonResponse::class)]);

        $this->responseBuilder->make($code = 'foo', $message = 'bar')->formatter($formatter)->respond();
        $this->responseFactory->shouldHaveReceived('make')->with(['code' => $code, 'message' => $message], 500, null)->once();
    }

    /**
     * Assert that [make] resolves an error code and message from config when given an exception.
     */
    public function testMakeMethodShouldAcceptAnException()
    {
        $formatter = mock(Formatter::class);
        $formatter->allows('error')->andReturnUsing(function ($response) {
            return ['code' => $response->code(), 'message' => $response->message()];
        });
        $this->config->allows(['get' => [InvalidArgumentException::class => [
            'code' => $code = 'foo',
            'status' => $status = 400,
        ]]]);
        $this->messageRegistry->allows(['resolve' => $message = 'bar']);
        $this->responseFactory->allows(['make' => mock(JsonResponse::class)]);

        $this->responseBuilder->make(new InvalidArgumentException)->formatter($formatter)->respond();

        $this->config->shouldHaveReceived('get')->with('responder.exceptions')->once();
        $this->messageRegistry->shouldHaveReceived('resolve')->with($code)->once();
        $this->responseFactory->shouldHaveReceived('make')->with(['code' => $code, 'message' => $message], $status, null)->once();
    }

    /**
     * Assert that [make] resolves an error code from an exception class name when no config is found.
     */
    public function testMakeMethodShouldResolveErrorCodeFromExceptionName()
    {
        $formatter = mock(Formatter::class);
        $formatter->allows('error')->andReturnUsing(function ($response) {
            return ['code' => $response->code(), 'message' => $response->message()];
        });
        $this->config->allows(['get' => null]);
        $this->messageRegistry->allows(['resolve' => $message = 'bar']);
        $this->responseFactory->allows(['make' => mock(JsonResponse::class)]);

        $this->responseBuilder->make(new InvalidArgumentException)->formatter($formatter)->respond();

        $this->responseFactory->shouldHaveReceived('make')->with(['code' => 'invalid_argument', 'message' => $message], 500, null)->once();
    }

    /**
     * Assert that [make] resolves an error message from an exception when no message is found.
     */
    public function testMakeMethodShouldResolveErrorMessageFromException()
    {
        $formatter = mock(Formatter::class);
        $formatter->allows('error')->andReturnUsing(function ($response) {
            return ['code' => $response->code(), 'message' => $response->message()];
        });
        $this->config->allows(['get' => [InvalidArgumentException::class => [
            'code' => $code = 'foo',
            'status' => $status = 400,
        ]]]);
        $this->messageRegistry->allows(['resolve' => $message = 'bar']);
        $this->responseFactory->allows(['make' => mock(JsonResponse::class)]);

        $this->responseBuilder->make(new InvalidArgumentException($message = 'bar'))->formatter($formatter)->respond();

        $this->responseFactory->shouldHaveReceived('make')->with(['code' => $code, 'message' => $message], $status, null)->once();
    }

    /**
     * Assert that [make] accepts an error code as first argument and exception as second.
     */
    public function testMakeMethodShouldAcceptAnErrorCodeAndException()
    {
        $formatter = mock(Formatter::class);
        $formatter->allows('error')->andReturnUsing(function ($response) {
            return ['code' => $response->code(), 'message' => $response->message()];
        });
        $this->config->allows(['get' => [InvalidArgumentException::class => [
            'code' => 'foo',
            'status' => $status = 400,
        ]]]);
        $this->messageRegistry->allows(['resolve' => $message = 'bar']);
        $this->responseFactory->allows(['make' => mock(JsonResponse::class)]);

        $this->responseBuilder->make($code = 'baz', new InvalidArgumentException)->formatter($formatter)->respond();

        $this->responseFactory->shouldHaveReceived('make')->with(['code' => $code, 'message' => $message], $status, null)->once();
    }
}
