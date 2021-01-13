<?php

namespace Flugg\Responder\Tests\Unit\Http\Normalizers;

use Flugg\Responder\Adapters\IlluminatePaginatorAdapter;
use Flugg\Responder\Http\Normalizers\ResourceNormalizer;
use Flugg\Responder\Http\Resources\Collection;
use Flugg\Responder\Http\Resources\Item;
use Flugg\Responder\Http\SuccessResponse;
use Flugg\Responder\Tests\UnitTestCase;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection as IlluminateCollection;

/**
 * Unit tests for the [ResourceNormalizer] class.
 *
 * @see \Flugg\Responder\Http\Normalizers\ResourceNormalizer
 */
class ResourceNormalizerTest extends UnitTestCase
{
    /**
     * Assert that [normalize] normalizes API resource to a success response.
     */
    public function testNormalizeMethodNormalizesResource()
    {
        $request = $this->mockRequest();
        $response = new Response(null, 200, ['x-foo' => 1]);
        $model = $this->mockModel([], $table = 'foo');
        $resource = $this->mockJsonResource($model, $data = ['foo' => 1], []);
        $resource->with($request->reveal())->willReturn($with = ['foo' => ['bar' => 2]]);
        $resource->additional = $additional = ['foo' => ['baz' => 3]];
        $resource->response()->willReturn($response);

        $result = (new ResourceNormalizer($resource->reveal(), $request->reveal()))->normalize();

        $this->assertInstanceOf(SuccessResponse::class, $result);
        $this->assertSame($response->status(), $result->status());
        $this->assertSame($response->headers->all(), $result->headers());
        $this->assertInstanceOf(Item::class, $result->resource());
        $this->assertSame($data, $result->resource()->data());
        $this->assertSame($table, $result->resource()->key());
        $this->assertSame(array_merge_recursive($with, $additional), $result->meta());
    }

    /**
     * Assert that [normalize] sets resource key to the results of [getResourceKey] on resource.
     */
    public function testNormalizeMethodSetsResourceKeyUsingMethodOnResource()
    {
        $request = $this->mockRequest();
        $model = $this->mockModel([], 'foo');
        $resource = $this->mockJsonResource($model, [], [], $key = 'bar');
        $resource->response()->willReturn(new Response);

        $result = (new ResourceNormalizer($resource->reveal(), $request->reveal()))->normalize();

        $this->assertSame($key, $result->resource()->key());
    }

    /**
     * Assert that [normalize] sets resource key to the results of [getResourceKey] on model.
     */
    public function testNormalizeMethodSetsResourceKeyUsingMethodOnModel()
    {
        $request = $this->mockRequest();
        $model = $this->mockModel([], 'foo', [], $key = 'bar');
        $resource = $this->mockJsonResource($model, [], []);
        $resource->response()->willReturn(new Response);

        $result = (new ResourceNormalizer($resource->reveal(), $request->reveal()))->normalize();

        $this->assertSame($key, $result->resource()->key());
    }

    /**
     * Assert that [normalize] normalizes API resource with nested resources.
     */
    public function testNormalizeMethodNormalizesResourceWithNestedResources()
    {
        $request = $this->mockRequest();
        $resource = $this->mockJsonResource($this->mockModel([], $table1 = 'foo'), $data1 = ['id' => 1], [
            ($key1 = 'foo') => $this->mockJsonResource($this->mockModel([], $table2 = 'bar'), $data2 = ['id' => 2], [
                ($key2 = 'bar') => $this->mockJsonResource($this->mockModel([], $table3 = 'baz'), $data3 = ['id' => 3])->reveal(),
            ])->reveal(),
        ]);
        $resource->response()->willReturn(new Response);

        $result = (new ResourceNormalizer($resource->reveal(), $request->reveal()))->normalize();

        $this->assertSame($data1, $result->resource()->data());
        $this->assertSame($table1, $result->resource()->key());
        $this->assertSame($data2, $result->resource()->relations()[$key1]->data());
        $this->assertSame($table2, $result->resource()->relations()[$key1]->key());
        $this->assertSame($data3, $result->resource()->relations()[$key1]->relations()[$key2]->data());
        $this->assertSame($table3, $result->resource()->relations()[$key1]->relations()[$key2]->key());
    }

    /**
     * Assert that [normalize] normalizes API resource with nested resource collections.
     */
    public function testNormalizeMethodNormalizesResourceWithNestedResourceCollection()
    {
        $request = $this->mockRequest();
        $resource = $this->mockJsonResource($this->mockModel([], $table1 = 'foo'), $data1 = ['id' => 1], [
            ($key1 = 'foo') => $this->mockResourceCollection([
                $this->mockJsonResource($this->mockModel([], $table2 = 'bar'), $data2 = ['id' => 2])->reveal(),
                $this->mockJsonResource($this->mockModel([], $table3 = 'baz'), $data3 = ['id' => 3])->reveal(),
            ])->reveal(),
        ]);
        $resource->response()->willReturn(new Response);

        $result = (new ResourceNormalizer($resource->reveal(), $request->reveal()))->normalize();

        $this->assertSame($data1, $result->resource()->data());
        $this->assertSame($table1, $result->resource()->key());
        $this->assertSame($data2, $result->resource()->relations()[$key1][0]->data());
        $this->assertSame($table2, $result->resource()->relations()[$key1][0]->key());
        $this->assertSame($data3, $result->resource()->relations()[$key1][1]->data());
        $this->assertSame($table3, $result->resource()->relations()[$key1][1]->key());
    }

    /**
     * Assert that [normalize] normalizes API resource collections to a success response.
     */
    public function testNormalizeMethodNormalizesResourceCollection()
    {
        $request = $this->mockRequest();
        $response = new Response(null, 200, ['x-foo' => 1]);
        $collection = $this->mockResourceCollection([
            $this->mockJsonResource($this->mockModel([], $table = 'foo'), $data1 = ['id' => 2])->reveal(),
            $this->mockJsonResource($this->mockModel([], $table), $data2 = ['id' => 3])->reveal(),
        ]);
        $collection->with($request->reveal())->willReturn($with = ['foo' => ['bar' => 2]]);
        $collection->additional = $additional = ['foo' => ['baz' => 3]];
        $collection->response()->willReturn($response);

        $result = (new ResourceNormalizer($collection->reveal(), $request->reveal()))->normalize();

        $this->assertInstanceOf(SuccessResponse::class, $result);
        $this->assertSame($response->status(), $result->status());
        $this->assertSame($response->headers->all(), $result->headers());
        $this->assertInstanceOf(Collection::class, $result->resource());
        $this->assertSame($data1, $result->resource()[0]->data());
        $this->assertSame($data2, $result->resource()[1]->data());
        $this->assertSame($table, $result->resource()->key());
        $this->assertSame(array_merge_recursive($with, $additional), $result->meta());
    }

    /**
     * Assert that [normalize] sets resource key to the results of [getResourceKey] from first resource.
     */
    public function testNormalizeMethodSetsResourceKeyUsingFirstResource()
    {
        $request = $this->mockRequest();
        $collection = $this->mockResourceCollection([
            $this->mockJsonResource($this->mockModel([], 'foo'), [], [], $key = 'bar')->reveal(),
            $this->mockJsonResource($this->mockModel([], 'baz'), [])->reveal(),
        ]);
        $collection->response()->willReturn(new Response);

        $result = (new ResourceNormalizer($collection->reveal(), $request->reveal()))->normalize();

        $this->assertSame($key, $result->resource()->key());
    }

    /**
     * Assert that [normalize] sets resource key to the results of [getResourceKey] from first model.
     */
    public function testNormalizeMethodSetsResourceKeyUsingFirstModel()
    {
        $request = $this->mockRequest();
        $collection = $this->mockResourceCollection([
            $this->mockJsonResource($this->mockModel([], 'foo', [], $key = 'bar'), [])->reveal(),
            $this->mockJsonResource($this->mockModel([], 'baz'), [])->reveal(),
        ]);
        $collection->response()->willReturn(new Response);

        $result = (new ResourceNormalizer($collection->reveal(), $request->reveal()))->normalize();

        $this->assertSame($key, $result->resource()->key());
    }

    /**
     * Assert that [normalize] sets resource key to null when no results are found.
     */
    public function testNormalizeMethodSetsResourceKeyToNullWhenEmpty()
    {
        $request = $this->mockRequest();
        $collection = $this->mockResourceCollection([]);
        $collection->response()->willReturn(new Response);

        $result = (new ResourceNormalizer($collection->reveal(), $request->reveal()))->normalize();

        $this->assertNull($result->resource()->key());
    }

    /**
     * Assert that [normalize] normalizes API resource collection with nested resource collections.
     */
    public function testNormalizeMethodNormalizesResourceCollectionWithNestedResources()
    {
        $request = $this->mockRequest();
        $collection = $this->mockResourceCollection([
            $this->mockJsonResource($this->mockModel([], $table1 = 'foo', []), $data1 = ['id' => 1], [
                ($key = 'foo') => $this->mockJsonResource($this->mockModel([], $table2 = 'bar'), $data2 = ['id' => 2])->reveal(),
            ])->reveal(),
        ]);
        $collection->response()->willReturn(new Response);

        $result = (new ResourceNormalizer($collection->reveal(), $request->reveal()))->normalize();

        $this->assertSame($table1, $result->resource()->key());
        $this->assertSame($data1, $result->resource()[0]->data());
        $this->assertSame($table1, $result->resource()[0]->key());
        $this->assertSame($table2, $result->resource()[0]->relations()[$key]->key());
        $this->assertSame($data2, $result->resource()[0]->relations()[$key]->data());
    }

    /**
     * Assert that [normalize] normalizes API resource collection with nested resource collections.
     */
    public function testNormalizeMethodNormalizesResourceCollectionWithNestedResourceCollection()
    {
        $request = $this->mockRequest();
        $collection = $this->mockResourceCollection([
            $this->mockJsonResource($this->mockModel([], $table1 = 'foo', []), $data1 = ['id' => 1], [
                ($key = 'foo') => $this->mockResourceCollection([
                    $this->mockJsonResource($this->mockModel([], $table2 = 'bar', []), $data2 = ['id' => 2])->reveal(),
                ])->reveal(),
            ])->reveal(),
        ]);
        $collection->response()->willReturn(new Response);

        $result = (new ResourceNormalizer($collection->reveal(), $request->reveal()))->normalize();

        $this->assertSame($table1, $result->resource()->key());
        $this->assertSame($data1, $result->resource()[0]->data());
        $this->assertSame($table1, $result->resource()[0]->key());
        $this->assertSame($table2, $result->resource()[0]->relations()[$key]->key());
        $this->assertSame($data2, $result->resource()[0]->relations()[$key][0]->data());
        $this->assertSame($table2, $result->resource()[0]->relations()[$key][0]->key());
    }

    /**
     * Assert that [normalize] normalizes API resource collections with paginator to a success response.
     */
    public function testNormalizeMethodNormalizesResourceCollectionWithPaginator()
    {
        $request = $this->mockRequest();
        $response = new Response(null, 200, ['x-foo' => 1]);
        $models = IlluminateCollection::make([
            $model1 = $this->mockModel([], $table = 'foo')->reveal(),
            $model2 = $this->mockModel([], $table)->reveal(),
        ]);
        $paginator = $this->mock(LengthAwarePaginator::class);
        $paginator->getCollection()->willReturn($models);
        $collection = $this->mockResourceCollection([
            $this->mockJsonResource($model1, [])->reveal(),
            $this->mockJsonResource($model2, [])->reveal(),
        ]);
        $collection->resource = $paginator->reveal();
        $collection->response()->willReturn($response);

        $result = (new ResourceNormalizer($collection->reveal(), $request->reveal()))->normalize();

        $this->assertInstanceOf(IlluminatePaginatorAdapter::class, $result->paginator());
    }
}
