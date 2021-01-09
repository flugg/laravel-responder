<?php

namespace Flugg\Responder\Tests\Unit\Http\Builders;

use Flugg\Responder\Contracts\Http\Formatter;
use Flugg\Responder\Contracts\Http\Normalizer;
use Flugg\Responder\Contracts\Http\ResponseFactory;
use Flugg\Responder\Contracts\Pagination\CursorPaginator;
use Flugg\Responder\Contracts\Pagination\Paginator;
use Flugg\Responder\Exceptions\InvalidDataException;
use Flugg\Responder\Http\Builders\SuccessResponseBuilder;
use Flugg\Responder\Http\Resources\Item;
use Flugg\Responder\Http\SuccessResponse;
use Flugg\Responder\Tests\IncreaseStatusByOneDecorator;
use Flugg\Responder\Tests\UnitTestCase;
use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Prophecy\Argument;
use stdClass;

/**
 * Unit tests for the [SuccessResponseBuilder] class.
 *
 * @see \Flugg\Responder\Http\Builders\SuccessResponseBuilder
 */
class SuccessResponseBuilderTest extends UnitTestCase
{
    /**
     * Mock of a [\Flugg\Responder\Contracts\Http\ResponseFactory] interface.
     *
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    protected $responseFactory;

    /**
     * Mock of an [\Illuminate\Contracts\Container\Container] interface.
     *
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    protected $container;

    /**
     * Mock of an [\Illuminate\Contracts\Config\Repository] class.
     *
     * @var \Prophecy\Prophecy\ObjectProphecy
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

        $this->responseFactory = $this->prophesize(ResponseFactory::class);
        $this->container = $this->prophesize(Container::class);
        $this->config = $this->prophesize(Repository::class);
        $this->responseBuilder = new SuccessResponseBuilder(
            $this->responseFactory->reveal(),
            $this->container->reveal(),
            $this->config->reveal()
        );
    }

    /**
     * Assert that [get] returns [SuccessResponse] object.
     */
    public function testGetMethodReturnsErrorResponseeObject()
    {
        $result = $this->responseBuilder->make()->get();

        $this->assertInstanceOf(SuccessResponse::class, $result);
    }

    /**
     * Assert that [make] creates a resource from an array and sets it on response object.
     */
    public function testMakeMethodCreatesResourceFromArray()
    {
        $result = $this->responseBuilder->make($data = ['foo' => 1]);

        $this->assertSame($this->responseBuilder, $result);
        $this->assertInstanceOf(Item::class, $result->get()->resource());
        $this->assertEquals($data, $result->get()->resource()->toArray());
    }

    /**
     * Assert that [make] .
     */
    public function testMakeMethodNormalizesDataUsingConfiguredNormalizer()
    {
        $normalizer = $this->prophesize(Normalizer::class);
        $normalizer->normalize()->willReturn($response = new SuccessResponse);
        $this->config->get('responder.normalizers')->willReturn([stdClass::class => get_class($normalizer->reveal())]);
        $this->container->makeWith(Argument::cetera())->willReturn($normalizer->reveal());

        $result = $this->responseBuilder->make($data = new stdClass)->get();

        $this->assertSame($response, $result);
        $this->container->makeWith(get_class($normalizer->reveal()), ['data' => $data])->shouldHaveBeenCalledOnce();
    }

    /**
     * Assert that [make] throws an exception when given non-supported data types.
     */
    public function testMakeMethodThrowsExceptionForInvalidDataType()
    {
        $this->expectException(InvalidDataException::class);

        $this->responseBuilder->make(123);
    }

    /**
     * Assert that [make] throws an exception when given data with no normalizer configured.
     */
    public function testMakeMethodThrowsExceptionForDataWithoutNormalizer()
    {
        $this->expectException(InvalidDataException::class);
        $this->config->get('responder.normalizers')->willReturn([]);

        $this->responseBuilder->make(new stdClass);
    }

    /**
     * Assert that [paginator] sets a paginator on response object.
     */
    public function testPaginatorMethodSetsPaginatorOnResponseObject()
    {
        $paginator = $this->prophesize(Paginator::class);

        $result = $this->responseBuilder->make()->paginator($paginator->reveal())->get();

        $this->assertEquals($paginator->reveal(), $result->paginator());
    }

    /**
     * Assert that [cursor] sets a cursor paginator on response object.
     */
    public function testCursorMethodSetsCursorPaginatorOnResponseObject()
    {
        $paginator = $this->prophesize(CursorPaginator::class);

        $result = $this->responseBuilder->make()->cursor($paginator->reveal())->get();

        $this->assertEquals($paginator->reveal(), $result->cursor());
    }

    /**
     * Assert that [respond] generates a response using [ResponseFactory].
     */
    public function testRespondMethodMakesResponse()
    {
        $this->responseFactory->make(Argument::cetera())->willReturn($response = new JsonResponse);

        $result = $this->responseBuilder->make()->respond($status = 300, $headers = ['x-foo' => 1]);

        $this->assertEquals($response, $result);
        $this->responseFactory->make([], $status, $headers)->shouldHaveBeenCalledOnce();
    }

    /**
     * Assert that [respond] defaults to a status code of 200.
     */
    public function testRespondMethodDefaultsToStatusCode200()
    {
        $this->responseFactory->make(Argument::cetera())->willReturn($response = new JsonResponse);

        $result = $this->responseBuilder->make()->respond();

        $this->assertEquals($response, $result);
        $this->responseFactory->make([], 200, [])->shouldHaveBeenCalledOnce();
    }

    /**
     * Assert that [toResponse] is an alternative of the [respond] method.
     */
    public function testToResponseMethodMakesResponse()
    {
        $this->responseFactory->make(Argument::cetera())->willReturn($response = new JsonResponse);

        $result = $this->responseBuilder->make()->toResponse(mock(Request::class));

        $this->assertEquals($response, $result);
        $this->responseFactory->make([], 200, [])->shouldHaveBeenCalledOnce();
    }

    /**
     * Assert that [toArray] returns response data as an array.
     */
    public function testToArrayMethodReturnsArray()
    {
        $response = new JsonResponse($data = ['foo' => 1]);
        $this->responseFactory->make(Argument::cetera())->willReturn($response);

        $result = $this->responseBuilder->make()->toArray();

        $this->assertEquals($data, $result);
    }

    /**
     * Assert that [toCollection] returns response data as a collection.
     */
    public function testToCollectionMethodReturnsCollection()
    {
        $response = new JsonResponse($data = ['foo' => 1]);
        $this->responseFactory->make(Argument::cetera())->willReturn($response);

        $result = $this->responseBuilder->make()->toCollection();

        $this->assertEquals(Collection::make($data), $result);
    }

    /**
     * Assert that [toJson] returns response data as a JSON string.
     */
    public function testToJsonMethodReturnsResponseAsJson()
    {
        $response = new JsonResponse($data = ['foo' => 1]);
        $this->responseFactory->make(Argument::cetera())->willReturn($response);

        $result = $this->responseBuilder->make()->toJson(JSON_PRETTY_PRINT);

        $this->assertEquals(json_encode($data, JSON_PRETTY_PRINT), $result);
    }

    /**
     * Assert that [JsonSerialize] returns response data as an array.
     */
    public function testJsonSerializeMethodReturnsResponseAsArray()
    {
        $response = new JsonResponse($data = ['foo' => 1]);
        $this->responseFactory->make(Argument::cetera())->willReturn($response);

        $result = $this->responseBuilder->make()->toArray();

        $this->assertEquals($data, $result);
    }

    /**
     * Assert that [formatter] sets response formatter.
     */
    public function testFormatterMethodSetsFormatter()
    {
        $formatter = $this->prophesize(Formatter::class);
        $formatter->success(Argument::any())->willReturn($data = ['foo' => 1]);
        $this->responseFactory->make(Argument::cetera())->willReturn(new JsonResponse);

        $this->responseBuilder->make()->formatter($formatter->reveal())->respond();

        $this->responseFactory->make($data, 200, [])->shouldHaveBeenCalledOnce();
    }

    /**
     * Assert that [formatter] resolves a formatter from the service container when given a string.
     */
    public function testFormatterMethodResolvesFormatterFromContainer()
    {
        $formatter = $this->prophesize(Formatter::class);
        $formatter->success(Argument::any())->willReturn($data = ['foo' => 1]);
        $this->container->make($binding = 'foo')->willReturn($formatter);
        $this->responseFactory->make(Argument::cetera())->willReturn(new JsonResponse);

        $this->responseBuilder->make()->formatter($binding)->respond();

        $this->responseFactory->make($data, 200, [])->shouldHaveBeenCalledOnce();
    }

    /**
     * Assert that [decorate] decorates the response.
     */
    public function testDecorateMethodDecoratesResponseFactory()
    {
        $this->responseFactory->make(Argument::cetera())->willReturn(new JsonResponse);

        $this->responseBuilder->make()->decorate(IncreaseStatusByOneDecorator::class)->respond();

        $this->responseFactory->make(Argument::any(), 201, [])->shouldHaveBeenCalledOnce();
    }

    /**
     * Assert that [decorate] accepts multiple decorators.
     */
    public function testDecorateMethodAcceptsMultipleDecorators()
    {
        $this->responseFactory->make(Argument::cetera())->willReturn(new JsonResponse);

        $this->responseBuilder->make()->decorate([
            IncreaseStatusByOneDecorator::class,
            IncreaseStatusByOneDecorator::class,
            IncreaseStatusByOneDecorator::class,
        ])->respond();

        $this->responseFactory->make(Argument::any(), 203, [])->shouldHaveBeenCalledOnce();
    }

    /**
     * Assert that [meta] sets metadata attached to the response.
     */
    public function testMetaMethodSetsMetadata()
    {
        $formatter = $this->prophesize(Formatter::class);
        $formatter->success(Argument::any())->will(function ($args) {
            return ['meta' => $args[0]->meta()];
        });
        $this->responseFactory->make(Argument::cetera())->willReturn(new JsonResponse);

        $this->responseBuilder->make()->meta($meta = ['foo' => 1])->formatter($formatter->reveal())->respond();

        $this->responseFactory->make(['meta' => $meta], 200, [])->shouldHaveBeenCalledOnce();
    }
}
