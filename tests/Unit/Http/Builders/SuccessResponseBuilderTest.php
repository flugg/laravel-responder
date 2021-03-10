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
use Flugg\Responder\Http\Resources\Primitive;
use Flugg\Responder\Http\Resources\Resource;
use Flugg\Responder\Http\SuccessResponse;
use Flugg\Responder\Tests\IncreaseStatusByOneDecorator;
use Flugg\Responder\Tests\UnitTestCase;
use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Http\JsonResponse;
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

        $this->responseFactory = $this->mock(ResponseFactory::class);
        $this->formatter = $this->mock(Formatter::class);
        $this->container = $this->mock(Container::class);
        $this->config = $this->mock(Repository::class);
        $this->responseBuilder = new SuccessResponseBuilder(
            $this->responseFactory->reveal(),
            $this->formatter->reveal(),
            $this->config->reveal(),
            $this->container->reveal(),
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
     * Assert that [make] creates a response by normalizing the data using configured normalizer.
     */
    public function testMakeMethodNormalizesDataUsingConfiguredNormalizer()
    {
        $normalizer = $this->mock(Normalizer::class);
        $normalizer->normalize()->willReturn($response = new SuccessResponse(new Item));
        $this->config->get('responder.normalizers')->willReturn([stdClass::class => get_class($normalizer->reveal())]);
        $this->container->makeWith(Argument::cetera())->willReturn($normalizer->reveal());

        $result = $this->responseBuilder->make($data = new stdClass)->get();

        $this->assertSame($response, $result);
        $this->container->makeWith(get_class($normalizer->reveal()), ['data' => $data])->shouldHaveBeenCalledOnce();
    }

    /**
     * Assert that [make] accepts a resource key which overrides resource key set on normalized response.
     */
    public function testMakeMethodResourceKeyParameterOverridesNormalizedResponse()
    {
        $normalizer = $this->mock(Normalizer::class);
        $normalizer->normalize()->willReturn($response = (new SuccessResponse(new Item([], 'foo'))));
        $this->config->get('responder.normalizers')->willReturn([stdClass::class => get_class($normalizer->reveal())]);
        $this->container->makeWith(Argument::cetera())->willReturn($normalizer->reveal());

        $this->responseBuilder->make(new stdClass, $key = 'bar')->get();

        $this->assertSame($key, $response->resource()->key());
    }

    /**
     * Assert that [make] creates an item resource from an array and sets it on response object.
     */
    public function testMakeMethodCreatesResourceFromArray()
    {
        $result = $this->responseBuilder->make($data = ['foo' => 1], $key = 'bar');

        $this->assertSame($this->responseBuilder, $result);
        $this->assertInstanceOf(Item::class, $result->get()->resource());
        $this->assertSame($data, $result->get()->resource()->data());
        $this->assertSame($key, $result->get()->resource()->key());
    }

    /**
     * Assert that [make] creates a primitve resource from a scalar and sets it on response object.
     */
    public function testMakeMethodCreatesResourceFromScalar()
    {
        foreach ([true, 1.0, 1, 'foo', null] as $data) {
            $result = $this->responseBuilder->make($data, $key = 'bar');

            $this->assertSame($this->responseBuilder, $result);
            $this->assertInstanceOf(Primitive::class, $result->get()->resource());
            $this->assertSame($data, $result->get()->resource()->data());
            $this->assertSame($key, $result->get()->resource()->key());
        }
    }

    /**
     * Assert that [make] throws an exception when given object with no normalizer configured.
     */
    public function testMakeMethodThrowsExceptionForObjectWithoutNormalizer()
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
        $paginator = $this->mock(Paginator::class);

        $result = $this->responseBuilder->make()->paginator($paginator->reveal())->get();

        $this->assertSame($paginator->reveal(), $result->paginator());
    }

    /**
     * Assert that [cursor] sets a cursor paginator on response object.
     */
    public function testCursorMethodSetsCursorPaginatorOnResponseObject()
    {
        $paginator = $this->mock(CursorPaginator::class);

        $result = $this->responseBuilder->make()->cursor($paginator->reveal())->get();

        $this->assertSame($paginator->reveal(), $result->cursor());
    }

    /**
     * Assert that [respond] generates a response using [ResponseFactory].
     */
    public function testRespondMethodMakesResponse()
    {
        $this->responseFactory->make(Argument::cetera())->willReturn($response = new JsonResponse);
        $this->formatter->success(Argument::cetera())->willReturn($data = ['foo' => 1]);
        $responseBuilder = $this->responseBuilder->make();

        $result = $responseBuilder->respond($status = 300, $headers = ['x-foo' => 1]);

        $this->assertSame($response, $result);
        $this->responseFactory->make($data, $status, $headers)->shouldHaveBeenCalledOnce();
        $this->formatter->success($responseBuilder->get())->shouldHaveBeenCalledOnce();
    }

    /**
     * Assert that [respond] defaults to a status code of 200.
     */
    public function testRespondMethodDefaultsToStatusCode200()
    {
        $this->responseFactory->make(Argument::cetera())->willReturn($response = new JsonResponse);
        $this->formatter->success(Argument::cetera())->willReturn($data = []);

        $result = $this->responseBuilder->make()->respond();

        $this->assertSame($response, $result);
        $this->responseFactory->make($data, 200, [])->shouldHaveBeenCalledOnce();
    }

    /**
     * Assert that [toResponse] is an alternative of the [respond] method.
     */
    public function testToResponseMethodMakesResponse()
    {
        $this->responseFactory->make(Argument::cetera())->willReturn($response = new JsonResponse);
        $this->formatter->success(Argument::cetera())->willReturn($data = ['foo' => 1]);

        $result = $this->responseBuilder->make()->toResponse($this->mockRequest());

        $this->assertSame($response, $result);
        $this->responseFactory->make($data, 200, [])->shouldHaveBeenCalledOnce();
    }

    /**
     * Assert that [toArray] returns response data as an array.
     */
    public function testToArrayMethodReturnsArray()
    {
        $this->responseFactory->make(Argument::cetera())->willReturn(new JsonResponse($data = ['foo' => 1]));
        $this->formatter->success(Argument::cetera())->willReturn([]);

        $result = $this->responseBuilder->make()->toArray();

        $this->assertSame($data, $result);
    }

    /**
     * Assert that [toCollection] returns response data as a collection.
     */
    public function testToCollectionMethodReturnsCollection()
    {
        $this->responseFactory->make(Argument::cetera())->willReturn(new JsonResponse($data = ['foo' => 1]));
        $this->formatter->success(Argument::cetera())->willReturn([]);

        $result = $this->responseBuilder->make()->toCollection();

        $this->assertEquals(Collection::make($data), $result);
    }

    /**
     * Assert that [toJson] returns response data as a JSON string.
     */
    public function testToJsonMethodReturnsResponseAsJson()
    {
        $this->responseFactory->make(Argument::cetera())->willReturn(new JsonResponse($data = ['foo' => 1]));
        $this->formatter->success(Argument::cetera())->willReturn([]);

        $result = $this->responseBuilder->make()->toJson(JSON_PRETTY_PRINT);

        $this->assertSame(json_encode($data, JSON_PRETTY_PRINT), $result);
    }

    /**
     * Assert that [JsonSerialize] returns response data as an array.
     */
    public function testJsonSerializeMethodReturnsResponseAsArray()
    {
        $this->responseFactory->make(Argument::cetera())->willReturn(new JsonResponse($data = ['foo' => 1]));
        $this->formatter->success(Argument::cetera())->willReturn([]);

        $result = $this->responseBuilder->make()->toArray();

        $this->assertSame($data, $result);
    }

    /**
     * Assert that [formatter] sets response formatter.
     */
    public function testFormatterMethodSetsFormatter()
    {
        $formatter = $this->mock(Formatter::class);
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
        $formatter = $this->mock(Formatter::class);
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
        $this->formatter->success(Argument::cetera())->willReturn([]);

        $this->responseBuilder->make()->decorate(IncreaseStatusByOneDecorator::class)->respond();

        $this->responseFactory->make(Argument::any(), 201, [])->shouldHaveBeenCalledOnce();
    }

    /**
     * Assert that [decorate] accepts multiple decorators.
     */
    public function testDecorateMethodAcceptsMultipleDecorators()
    {
        $this->responseFactory->make(Argument::cetera())->willReturn(new JsonResponse);
        $this->formatter->success(Argument::cetera())->willReturn([]);

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
        $result = $this->responseBuilder->make()->meta($meta = ['foo' => 1])->get();

        $this->assertSame($meta, $result->meta());
    }
}
