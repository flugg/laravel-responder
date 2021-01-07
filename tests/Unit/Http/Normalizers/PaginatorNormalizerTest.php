<?php

namespace Flugg\Responder\Tests\Unit\Http\Normalizers;

use Flugg\Responder\Adapters\IlluminatePaginatorAdapter;
use Flugg\Responder\Http\Normalizers\PaginatorNormalizer;
use Flugg\Responder\Http\Resources\Collection;
use Flugg\Responder\Http\SuccessResponse;
use Flugg\Responder\Tests\ModelWithGetResourceKey;
use Flugg\Responder\Tests\UnitTestCase;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection as IlluminateCollection;

/**
 * Unit tests for the [Flugg\Responder\Http\Normalizers\PaginatorNormalizer] class.
 *
 * @see \Flugg\Responder\Http\Normalizers\PaginatorNormalizer
 */
class PaginatorNormalizerTest extends UnitTestCase
{
    /**
     * Assert that [normalize] normalizes paginator to a success response value object.
     */
    public function testNormalizeMethodNormalizesPaginator()
    {
        $paginator = mock(LengthAwarePaginator::class);
        $paginator->allows(['getCollection' => $collection = mock(IlluminateCollection::class)]);
        $collection->allows([
            'isEmpty' => false,
            'all' => [$model1 = mock(Model::class), $model2 = mock(Model::class)],
            'first' => $model1,
        ]);
        $model1->allows([
            'getTable' => $key = 'foo',
            'getRelations' => [],
            'withoutRelations' => $model1,
            'toArray' => $data1 = ['foo' => 123],
        ]);
        $model2->allows([
            'getTable' => 'bar',
            'getRelations' => [],
            'withoutRelations' => $model2,
            'toArray' => $data2 = ['bar' => 456],
        ]);
        $normalizer = new PaginatorNormalizer($paginator);

        $result = $normalizer->normalize();
        $resource = $result->resource();

        $this->assertInstanceOf(SuccessResponse::class, $result);
        $this->assertInstanceOf(IlluminatePaginatorAdapter::class, $result->paginator());
        $this->assertInstanceOf(Collection::class, $resource);
        $this->assertEquals(200, $result->status());
        $this->assertEquals([$data1, $data2], $resource->toArray());
        $this->assertEquals($key, $result->resource()->key());
        if ($resource instanceof Collection) {
            $this->assertCount(2, $resource->items());
        }
    }

    /**
     * Assert that [normalize] sets resource key to the results of [getResourceKey] on first model.
     */
    public function testNormalizeMethodSetsResourceKeyUsingMethodOnFirstModel()
    {
        $paginator = mock(LengthAwarePaginator::class);
        $paginator->allows(['getCollection' => $collection = mock(IlluminateCollection::class)]);
        $collection->allows([
            'isEmpty' => false,
            'all' => [$model = mock(ModelWithGetResourceKey::class)],
            'first' => $model,
        ]);
        $model->allows([
            'getResourceKey' => $key = 'foo',
            'getTable' => 'bar',
            'getRelations' => [],
            'withoutRelations' => $model,
            'toArray' => [],
        ]);
        $normalizer = new PaginatorNormalizer($paginator);

        $result = $normalizer->normalize();

        $this->assertEquals($key, $result->resource()->key());
    }

    /**
     * Assert that [normalize] sets resource key to null when no results are found.
     */
    public function testNormalizeMethodSetsResourceKeyToNullWhenNoResults()
    {
        $paginator = mock(LengthAwarePaginator::class);
        $paginator->allows(['getCollection' => $collection = mock(IlluminateCollection::class)]);
        $collection->allows([
            'isEmpty' => true,
            'all' => [],
        ]);
        $normalizer = new PaginatorNormalizer($paginator);

        $result = $normalizer->normalize();

        $this->assertNull($result->resource()->key());
    }

    /**
     * Assert that [normalize] normalizes Eloquent collection with item relation.
     */
    public function testNormalizeMethodNormalizesEloquentCollectionWithItemRelation()
    {
        $paginator = mock(LengthAwarePaginator::class);
        $paginator->allows(['getCollection' => $collection = mock(IlluminateCollection::class)]);
        $collection->allows([
            'isEmpty' => false,
            'all' => [$model = mock(Model::class)],
            'first' => $model,
        ]);
        $model->allows([
            'getTable' => 'foo',
            'getRelations' => ['bar' => $relation = mock(Model::class)],
            'withoutRelations' => $model,
            'toArray' => [],
        ]);
        $relation->allows([
            'getTable' => 'bar',
            'getRelations' => [],
            'withoutRelations' => $relation,
            'toArray' => $relatedData = ['bar' => 456],
        ]);
        $normalizer = new PaginatorNormalizer($paginator);

        $result = $normalizer->normalize();
        $resource = $result->resource();

        $this->assertInstanceOf(Collection::class, $resource);
        if ($resource instanceof Collection) {
            $this->assertEquals($relatedData, $resource->items()[0]->relations()['bar']->toArray());
        }
    }

    /**
     * Assert that [normalize] normalizes Eloquent collection with collection relation.
     */
    public function testNormalizeMethodNormalizesEloquentCollectionWithCollectionRelation()
    {
        $paginator = mock(LengthAwarePaginator::class);
        $paginator->allows(['getCollection' => $collection = mock(IlluminateCollection::class)]);
        $collection->allows([
            'isEmpty' => false,
            'all' => [$model = mock(Model::class)],
            'first' => $model,
        ]);
        $model->allows([
            'getTable' => 'foo',
            'getRelations' => ['bar' => $relatedCollection = mock(EloquentCollection::class)],
            'withoutRelations' => $model,
            'toArray' => [],
        ]);
        $relatedCollection->allows([
            'isEmpty' => false,
            'all' => [$relation = mock(Model::class)],
            'first' => $relation,
        ]);
        $relation->allows([
            'getTable' => 'bar',
            'getRelations' => [],
            'withoutRelations' => $relation,
            'toArray' => $relatedData = ['bar' => 456],
        ]);
        $normalizer = new PaginatorNormalizer($paginator);

        $result = $normalizer->normalize();
        $resource = $result->resource();

        $this->assertInstanceOf(Collection::class, $resource);
        if ($resource instanceof Collection) {
            $this->assertEquals([$relatedData], $resource->items()[0]->relations()['bar']->toArray());
        }
    }
}
