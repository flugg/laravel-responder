<?php

namespace Flugg\Responder\Tests\Unit\Http\Builders;

use Flugg\Responder\Contracts\ErrorMessageRegistry;
use Flugg\Responder\Contracts\Http\Formatter;
use Flugg\Responder\Contracts\Http\ResponseFactory;
use Flugg\Responder\Contracts\Validation\Validator;
use Flugg\Responder\Http\Builders\ErrorResponseBuilder;
use Flugg\Responder\Http\ErrorResponse;
use Flugg\Responder\Tests\IncreaseStatusByOneDecorator;
use Flugg\Responder\Tests\UnitTestCase;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Prophecy\Argument;

/**
 * Unit tests for the [ErrorResponseBuilder] class.
 *
 * @see \Flugg\Responder\Http\Builders\ErrorResponseBuilder
 */
class ErrorResponseBuilderTest extends UnitTestCase
{
    /**
     * Mock of a [\Flugg\Responder\Contracts\Http\ResponseFactory] interface.
     *
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    protected $responseFactory;

    /**
     * Mock of a [\Flugg\Responder\Contracts\Http\Formatter] interface.
     *
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    protected $formatter;

    /**
     * Mock of an [\Illuminate\Contracts\Config\Repository] class.
     *
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    protected $config;

    /**
     * Mock of an [\Illuminate\Contracts\Container\Container] interface.
     *
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    protected $container;

    /**
     * Mock of a [\Flugg\Responder\Contracts\ErrorMessageRegistry] interface.
     *
     * @var \Prophecy\Prophecy\ObjectProphecy
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

        $this->responseFactory = $this->mock(ResponseFactory::class);
        $this->formatter = $this->mock(Formatter::class);
        $this->config = $this->mock(Repository::class);
        $this->container = $this->mock(Container::class);
        $this->messageRegistry = $this->mock(ErrorMessageRegistry::class);
        $this->responseBuilder = new ErrorResponseBuilder(
            $this->responseFactory->reveal(),
            $this->formatter->reveal(),
            $this->config->reveal(),
            $this->container->reveal(),
            $this->messageRegistry->reveal()
        );
    }

    /**
     * Assert that [get] returns an [ErrorResponse] object.
     */
    public function testGetMethodReturnsErrorResponseeObject()
    {
        $result = $this->responseBuilder->make()->get();

        $this->assertInstanceOf(ErrorResponse::class, $result);
    }

    /**
     * Assert that [make] sets error code and message on response object.
     */
    public function testMakeMethodSetsErrorCodeAndMessage()
    {
        $result = $this->responseBuilder->make($code = 'foo', $message = 'bar');

        $this->assertSame($this->responseBuilder, $result);
        $this->assertSame($code, $result->get()->code());
        $this->assertSame($message, $result->get()->message());
    }

    /**
     * Assert that [make] resolves an error code and message from config when given an exception.
     */
    public function testMakeMethodAcceptsAnException()
    {
        $this->config->get('responder.exceptions')->willReturn([InvalidArgumentException::class => [
            'code' => $code = 'foo',
            'status' => $status = 400,
        ]]);
        $this->messageRegistry->resolve($code)->willReturn($message = 'bar');
        $this->responseFactory->make(Argument::cetera())->willReturn(new JsonResponse);

        $result = $this->responseBuilder->make(new InvalidArgumentException)->get();

        $this->assertSame($code, $result->code());
        $this->assertSame($message, $result->message());
        $this->assertSame($status, $result->status());
    }

    /**
     * Assert that [make] resolves an error code from an exception class name when no config is found.
     */
    public function testMakeMethodResolvesErrorCodeFromExceptionName()
    {
        $code = 'invalid_argument';
        $this->config->get('responder.exceptions')->willReturn([]);
        $this->messageRegistry->resolve($code)->willReturn($message = 'bar');
        $this->responseFactory->make(Argument::cetera())->willReturn(new JsonResponse);

        $result = $this->responseBuilder->make(new InvalidArgumentException)->get();

        $this->assertSame($code = 'invalid_argument', $result->code());
        $this->assertSame($message, $result->message());
        $this->assertSame(500, $result->status());
    }

    /**
     * Assert that [make] resolves an error message from an exception when no message is found.
     */
    public function testMakeMethodResolvesErrorMessageFromException()
    {
        $this->config->get('responder.exceptions')->willReturn([InvalidArgumentException::class => [
            'code' => $code = 'foo',
            'status' => $status = 400,
        ]]);
        $this->messageRegistry->resolve($code)->willReturn(null);
        $this->responseFactory->make(Argument::cetera())->willReturn(new JsonResponse);

        $result = $this->responseBuilder->make(new InvalidArgumentException($message = 'bar'))->get();

        $this->assertSame($code, $result->code());
        $this->assertSame($message, $result->message());
        $this->assertSame($status, $result->status());
    }

    /**
     * Assert that [make] accepts an error code as first argument and exception as second.
     */
    public function testMakeMethodAcceptsAnErrorCodeAndException()
    {
        $this->config->get('responder.exceptions')->willReturn([InvalidArgumentException::class => [
            'code' => 'foo',
            'status' => $status = 400,
        ]]);
        $this->messageRegistry->resolve($code = 'baz')->willReturn($message = 'bar');
        $this->responseFactory->make(Argument::cetera())->willReturn(new JsonResponse);

        $result = $this->responseBuilder->make($code, new InvalidArgumentException)->get();

        $this->assertSame($code, $result->code());
        $this->assertSame($message, $result->message());
        $this->assertSame($status, $result->status());
    }

    /**
     * Assert that [validator] sets a validator on response object.
     */
    public function testValidatorMethodSetsValidatorOnResponseObject()
    {
        $validator = $this->mock(Validator::class);

        $result = $this->responseBuilder->make()->validator($validator->reveal())->get();

        $this->assertSame($validator->reveal(), $result->validator());
    }

    /**
     * Assert that [respond] generates a response using [ResponseFactory].
     */
    public function testRespondMethodMakesResponse()
    {
        $this->responseFactory->make(Argument::cetera())->willReturn($response = new JsonResponse);
        $this->formatter->error(Argument::cetera())->willReturn($data = ['foo' => 1]);
        $responseBuilder = $this->responseBuilder->make();

        $result = $responseBuilder->respond($status = 400, $headers = ['x-foo' => 1]);

        $this->assertSame($response, $result);
        $this->responseFactory->make($data, $status, $headers)->shouldHaveBeenCalledOnce();
        $this->formatter->error($responseBuilder->get())->shouldHaveBeenCalledOnce();
    }

    /**
     * Assert that [respond] defaults to a status code of 500.
     */
    public function testRespondMethodDefaultsToStatusCode500()
    {
        $this->responseFactory->make(Argument::cetera())->willReturn($response = new JsonResponse);
        $this->formatter->error(Argument::cetera())->willReturn($data = []);

        $result = $this->responseBuilder->make()->respond();

        $this->assertSame($response, $result);
        $this->responseFactory->make($data, 500, [])->shouldHaveBeenCalledOnce();
    }

    /**
     * Assert that [toResponse] is an alternative of the [respond] method.
     */
    public function testToResponseMethodMakesResponse()
    {
        $this->responseFactory->make(Argument::cetera())->willReturn($response = new JsonResponse);
        $this->formatter->error(Argument::cetera())->willReturn($data = ['foo' => 1]);

        $result = $this->responseBuilder->make()->toResponse($this->mockRequest());

        $this->assertSame($response, $result);
        $this->responseFactory->make($data, 500, [])->shouldHaveBeenCalledOnce();
    }

    /**
     * Assert that [toArray] returns response data as an array.
     */
    public function testToArrayMethodReturnsArray()
    {
        $this->responseFactory->make(Argument::cetera())->willReturn(new JsonResponse($data = ['foo' => 1]));
        $this->formatter->error(Argument::cetera())->willReturn([]);

        $result = $this->responseBuilder->make()->toArray();

        $this->assertSame($data, $result);
    }

    /**
     * Assert that [toCollection] returns response data as a collection.
     */
    public function testToCollectionMethodReturnsCollection()
    {
        $this->responseFactory->make(Argument::cetera())->willReturn(new JsonResponse($data = ['foo' => 1]));
        $this->formatter->error(Argument::cetera())->willReturn([]);

        $result = $this->responseBuilder->make()->toCollection();

        $this->assertEquals(Collection::make($data), $result);
    }

    /**
     * Assert that [toJson] returns response data as a JSON string.
     */
    public function testToJsonMethodReturnsResponseAsJson()
    {
        $this->responseFactory->make(Argument::cetera())->willReturn(new JsonResponse($data = ['foo' => 1]));
        $this->formatter->error(Argument::cetera())->willReturn([]);

        $result = $this->responseBuilder->make()->toJson(JSON_PRETTY_PRINT);

        $this->assertSame(json_encode($data, JSON_PRETTY_PRINT), $result);
    }

    /**
     * Assert that [JsonSerialize] returns response data as an array.
     */
    public function testJsonSerializeMethodReturnsResponseAsArray()
    {
        $this->responseFactory->make(Argument::cetera())->willReturn(new JsonResponse($data = ['foo' => 1]));
        $this->formatter->error(Argument::cetera())->willReturn([]);

        $result = $this->responseBuilder->make()->toArray();

        $this->assertSame($data, $result);
    }

    /**
     * Assert that [formatter] sets response formatter.
     */
    public function testFormatterMethodSetsFormatter()
    {
        $formatter = $this->mock(Formatter::class);
        $formatter->error(Argument::any())->willReturn($data = ['foo' => 1]);
        $this->responseFactory->make(Argument::cetera())->willReturn(new JsonResponse);

        $this->responseBuilder->make()->formatter($formatter->reveal())->respond();

        $this->responseFactory->make($data, 500, [])->shouldHaveBeenCalledOnce();
    }

    /**
     * Assert that [formatter] resolves a formatter from the service container when given a string.
     */
    public function testFormatterMethodResolvesFormatterFromContainer()
    {
        $formatter = $this->mock(Formatter::class);
        $formatter->error(Argument::any())->willReturn($data = ['foo' => 1]);
        $this->container->make($binding = 'foo')->willReturn($formatter);
        $this->responseFactory->make(Argument::cetera())->willReturn(new JsonResponse);

        $this->responseBuilder->make()->formatter($binding)->respond();

        $this->responseFactory->make($data, 500, [])->shouldHaveBeenCalledOnce();
    }

    /**
     * Assert that [decorate] decorates the response.
     */
    public function testDecorateMethodDecoratesResponseFactory()
    {
        $this->responseFactory->make(Argument::cetera())->willReturn(new JsonResponse);
        $this->formatter->error(Argument::cetera())->willReturn([]);

        $this->responseBuilder->make()->decorate(IncreaseStatusByOneDecorator::class)->respond();

        $this->responseFactory->make(Argument::any(), 501, [])->shouldHaveBeenCalledOnce();
    }

    /**
     * Assert that [decorate] accepts multiple decorators.
     */
    public function testDecorateMethodAcceptsMultipleDecorators()
    {
        $this->responseFactory->make(Argument::cetera())->willReturn(new JsonResponse);
        $this->formatter->error(Argument::cetera())->willReturn([]);

        $this->responseBuilder->make()->decorate([
            IncreaseStatusByOneDecorator::class,
            IncreaseStatusByOneDecorator::class,
            IncreaseStatusByOneDecorator::class,
        ])->respond();

        $this->responseFactory->make(Argument::any(), 503, [])->shouldHaveBeenCalledOnce();
    }

    /**
     * Assert that [meta] sets metadata attached to the response.
     */
    public function testMetaMethodSetsMetadata()
    {
        $result = $this->responseBuilder->make()->meta($meta = ['foo' => 1])->get();

        $this->assertSame($meta, $result->meta());
    }
}
