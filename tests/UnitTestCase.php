<?php

namespace Flugg\Responder\Tests;

use Flugg\Responder\Contracts\Pagination\CursorPaginator;
use Flugg\Responder\Contracts\Pagination\Paginator;
use Flugg\Responder\Contracts\Validation\Validator;
use Flugg\Responder\Http\Builders\ErrorResponseBuilder;
use Flugg\Responder\Http\Decorators\ResponseDecorator;
use Flugg\Responder\Http\ErrorResponse;
use Flugg\Responder\Http\Resources\Collection;
use Flugg\Responder\Http\Resources\Item;
use Flugg\Responder\Http\SuccessResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * Abstract test case for bootstrapping the environment for the unit suite.
 */
abstract class UnitTestCase extends TestCase
{
    use MockeryPHPUnitIntegration;
    use ProphecyTrait;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        Mockery::globalHelpers();
    }

    /**
     * Make a Prophecy mock of the given class or interface.
     *
     * @param string|null $classOrInterface
     * @return \Prophecy\Prophecy\ObjectProphecy
     */
    protected function mock(?string $classOrInterface = null): ObjectProphecy
    {
        return $this->prophesize($classOrInterface);
    }

    /**
     * Make a Prophecy mock of an [\Illuminate\Http\Request] class.
     *
     * @param bool $json
     * @return \Prophecy\Prophecy\ObjectProphecy
     */
    protected function mockRequest($json = true): ObjectProphecy
    {
        return tap($this->prophesize(Request::class), function ($request) use ($json) {
            $request->expectsJson()->willReturn($json);
        });
    }

    /**
     * Make a Prophecy mock of a [\Flugg\Responder\Http\Builders\ErrorResponseBuilder] class.
     *
     * @param \Illuminate\Http\JsonResponse $response
     * @return \Prophecy\Prophecy\ObjectProphecy
     */
    protected function mockErrorResponseBuilder(JsonResponse $response): ObjectProphecy
    {
        return tap($this->prophesize(ErrorResponseBuilder::class), function ($responseBuilder) use ($response) {
            $responseBuilder->respond()->willReturn($response);
        });
    }

    /**
     * Make a Prophecy mock of a [\Flugg\Responder\Http\SuccessResponse] class.
     *
     * @param \Flugg\Responder\Http\Resources\Resource|\Prophecy\Prophecy\ObjectProphecy $resource
     * @param array $meta
     * @return \Prophecy\Prophecy\ObjectProphecy
     */
    protected function mockSuccessResponse($resource, array $meta = []): ObjectProphecy
    {
        return tap($this->mock(SuccessResponse::class), function ($response) use ($resource, $meta) {
            $response->resource()->willReturn($resource);
            $response->meta()->willReturn($meta);
            $response->paginator()->willReturn(null);
            $response->cursor()->willReturn(null);
        });
    }

    /**
     * Make a Prophecy mock of a [\Flugg\Responder\Http\ErrorResponse] class.
     *
     * @param string|null $code
     * @param string|null $message
     * @param array $meta
     * @return \Prophecy\Prophecy\ObjectProphecy
     */
    protected function mockErrorResponse(?string $code = null, ?string $message = null, array $meta = []): ObjectProphecy
    {
        return tap($this->mock(ErrorResponse::class), function ($response) use ($code, $message, $meta) {
            $response->code()->willReturn($code);
            $response->message()->willReturn($message);
            $response->meta()->willReturn($meta);
            $response->validator()->willReturn(null);
        });
    }

    /**
     * Make a Prophecy mock of a [\Flugg\Responder\Http\Resources\Item] class.
     *
     * @param array $data
     * @param string|null $key
     * @param array $relations
     * @return \Prophecy\Prophecy\ObjectProphecy
     */
    protected function mockItem(array $data, ?string $key = null, $relations = []): ObjectProphecy
    {
        return tap($this->mock(Item::class), function ($item) use ($data, $key, $relations) {
            $item->data()->willReturn($data);
            $item->key()->willReturn($key);
            $item->relations()->willReturn($relations);
        });
    }

    /**
     * Make a Prophecy mock of a [\Flugg\Responder\Http\Resources\Collection] class.
     *
     * @param \Flugg\Responder\Http\Resources\Item[]|\Prophecy\Prophecy\ObjectProphecy[] $items
     * @param string|null $key
     * @return \Prophecy\Prophecy\ObjectProphecy
     */
    protected function mockCollection(array $items = [], ?string $key = null): ObjectProphecy
    {
        return tap($this->mock(Collection::class), function ($item) use ($items, $key) {
            $item->items()->willReturn($items);
            $item->key()->willReturn($key);
        });
    }

    /**
     * Make a Prophecy mock of a [\Flugg\Responder\Contracts\Pagination\Paginator] interface.
     *
     * @param int $count
     * @param int $total
     * @param int $perPage
     * @param int $currentPage
     * @param int $lastPage
     * @return \Prophecy\Prophecy\ObjectProphecy
     */
    protected function mockPaginator(int $count, int $total, int $perPage, int $currentPage, int $lastPage): ObjectProphecy
    {
        return tap($this->mock(Paginator::class), function ($paginator) use ($count, $total, $perPage, $currentPage, $lastPage) {
            $paginator->count()->willReturn($count);
            $paginator->total()->willReturn($total);
            $paginator->perPage()->willReturn($perPage);
            $paginator->currentPage()->willReturn($currentPage);
            $paginator->lastPage()->willReturn($lastPage);
        });
    }

    /**
     * Make a Prophecy mock of a [\Flugg\Responder\Contracts\Pagination\CursorPaginator] interface.
     *
     * @param int $count
     * @param mixed $current
     * @param mixed $previous
     * @param mixed $next
     * @return \Prophecy\Prophecy\ObjectProphecy
     */
    protected function mockCursor(int $count, $current, $previous, $next): ObjectProphecy
    {
        return tap($this->mock(CursorPaginator::class), function ($cursor) use ($count, $current, $previous, $next) {
            $cursor->count()->willReturn($count);
            $cursor->current()->willReturn($current);
            $cursor->previous()->willReturn($previous);
            $cursor->next()->willReturn($next);
        });
    }

    /**
     * Make a Prophecy mock of a [\Flugg\Responder\Contracts\Validation\Validator] interface.
     *
     * @param array $failed
     * @param array $errors
     * @param array $messages
     * @return \Prophecy\Prophecy\ObjectProphecy
     */
    protected function mockValidator(array $failed = [], array $errors = [], array $messages): ObjectProphecy
    {
        return tap($this->mock(Validator::class), function ($validator) use ($failed, $errors, $messages) {
            $validator->failed()->willReturn($failed);
            $validator->errors()->willReturn($errors);
            $validator->messages()->willReturn($messages);
        });
    }
}

/** Stub model with a [getResourceKey] method. */
class ModelWithGetResourceKey extends Model
{
    public function getResourceKey()
    {
        //
    }
}

/** Stub class to increase status code by one. */
class IncreaseStatusByOneDecorator extends ResponseDecorator
{
    public function make(array $data, int $status, array $headers = []): JsonResponse
    {
        return parent::make($data, $status + 1, $headers);
    }
}
