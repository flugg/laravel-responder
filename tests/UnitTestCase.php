<?php

namespace Flugg\Responder\Tests;

use Flugg\Responder\Contracts\Pagination\CursorPaginator;
use Flugg\Responder\Contracts\Pagination\Paginator;
use Flugg\Responder\Contracts\Validation\Validator;
use Flugg\Responder\Http\Decorators\ResponseDecorator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection as IlluminateCollection;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * Abstract test case for bootstrapping the environment for the unit suite.
 */
abstract class UnitTestCase extends TestCase
{
    use ProphecyTrait;

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
        return tap($this->mock(Paginator::class), function (ObjectProphecy $paginator) use ($count, $total, $perPage, $currentPage, $lastPage) {
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
        return tap($this->mock(CursorPaginator::class), function (ObjectProphecy $cursor) use ($count, $current, $previous, $next) {
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
        return tap($this->mock(Validator::class), function (ObjectProphecy $validator) use ($failed, $errors, $messages) {
            $validator->failed()->willReturn($failed);
            $validator->errors()->willReturn($errors);
            $validator->messages()->willReturn($messages);
        });
    }

    /**
     * Make a Prophecy mock of an [\Illuminate\Http\Request] class.
     *
     * @param bool $json
     * @return \Prophecy\Prophecy\ObjectProphecy
     */
    protected function mockRequest($json = true): ObjectProphecy
    {
        return tap($this->prophesize(Request::class), function (ObjectProphecy $request) use ($json) {
            $request->expectsJson()->willReturn($json);
        });
    }

    /**
     * Make a Prophecy mock of an [\Illuminate\Database\Eloquent\Model] class.
     *
     * @param array $data
     * @param string $table
     * @param string|null $key
     * @param array $relations
     * @param string $key
     * @return \Prophecy\Prophecy\ObjectProphecy
     */
    protected function mockModel(array $data = [], string $table, $relations = [], ?string $key = null): ObjectProphecy
    {
        return tap($this->mock(Model::class), function (ObjectProphecy $model) use ($data, $table, $key, $relations) {
            if ($key !== null) {
                $model->willImplement(HasResourceKey::class);
                $model->getResourceKey()->willReturn($key);
            }
            $model->toArray()->willReturn($data);
            $model->getTable()->willReturn($table);
            $model->getRelations()->willReturn($relations);
            $model->withoutRelations()->willReturn($model);
        });
    }

    /**
     * Make a Prophecy mock of a one-to-one [\Illuminate\Database\Eloquent\Relations\Relation] class.
     *
     * @param string $relationClass
     * @param \Illuminate\Database\Eloquent\Model|\Prophecy\Prophecy\ObjectProphecy $model
     * @return \Prophecy\Prophecy\ObjectProphecy
     */
    protected function mockOneToOneRelation(string $relationClass, $model): ObjectProphecy
    {
        return tap($this->mock($relationClass), function (ObjectProphecy $relation) use ($model) {
            $relation->willImplement(ExtendsQueryBuilder::class);
            $relation->first()->willReturn($model);
        });
    }

    /**
     * Make a Prophecy mock of a many-to-many [\Illuminate\Database\Eloquent\Relations\Relation] class.
     *
     * @param string $relationClass
     * @param \Illuminate\Database\Eloquent\Collection|\Prophecy\Prophecy\ObjectProphecy $collection
     * @return \Prophecy\Prophecy\ObjectProphecy
     */
    protected function mockManyToManyRelation(string $relationClass, $collection): ObjectProphecy
    {
        return tap($this->mock($relationClass), function (ObjectProphecy $relation) use ($collection) {
            $relation->willImplement(ExtendsQueryBuilder::class);
            $relation->get()->willReturn($collection);
        });
    }

    /**
     * Make a Prophecy mock of an [\Illuminate\Http\Resources\Json\JsonResource] class.
     *
     * @param mixed $model
     * @param array $data
     * @param array $relations
     * @param array $with
     * @param string|null $key
     * @param mixed|null $item
     * @return \Prophecy\Prophecy\ObjectProphecy
     */
    protected function mockJsonResource($item = null, array $data, array $relations = [], ?string $key = null): ObjectProphecy
    {
        return tap($this->mock(JsonResource::class), function (ObjectProphecy $resource) use ($item, $data, $relations, $key) {
            if ($key !== null) {
                $resource->willImplement(HasResourceKey::class);
                $resource->getResourceKey()->willReturn($key);
            }
            $resource->resource = $item;
            $resource->resolve()->willReturn(array_merge($data, array_map(function ($relation) {
                return $relation instanceof ResourceCollection ? array_map(function ($resource) {
                    return $resource->resolve();
                }, $relation->collection->all()) : $relation->resolve();
            }, $relations)));
            $resource->toArray(Argument::any())->willReturn(array_merge($data, $relations));
            $resource->with(Argument::any())->willReturn([]);
        });
    }

    /**
     * Make a Prophecy mock of an [\Illuminate\Http\Resources\Json\ResourceCollection] class.
     *
     * @param \Illuminate\Http\Resources\Json\JsonResource[]|\Prophecy\Prophecy\ObjectProphecy[] $resources
     * @return \Prophecy\Prophecy\ObjectProphecy
     */
    protected function mockResourceCollection($resources): ObjectProphecy
    {
        return tap($this->mock(ResourceCollection::class), function (ObjectProphecy $collection) use ($resources) {
            $collection->collection = IlluminateCollection::make($resources);
            $collection->with(Argument::any())->willReturn([]);
        });
    }
}

/** Stub interface with a [getResourceKey] method. */
interface HasResourceKey
{
    public function getResourceKey();
}

/** Stub interface with a [first] and [get] method to mimick a query builder. */
interface ExtendsQueryBuilder
{
    public function first();

    public function get();
}

/** Stub class to increase status code by one. */
class IncreaseStatusByOneDecorator extends ResponseDecorator
{
    public function make(array $data, int $status, array $headers = []): JsonResponse
    {
        return parent::make($data, $status + 1, $headers);
    }
}
