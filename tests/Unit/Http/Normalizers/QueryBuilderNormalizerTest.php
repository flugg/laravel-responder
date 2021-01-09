<?php

namespace Flugg\Responder\Tests\Unit\Http\Normalizers;

use Flugg\Responder\Http\Normalizers\QueryBuilderNormalizer;
use Flugg\Responder\Http\Resources\Collection;
use Flugg\Responder\Http\Resources\Item;
use Flugg\Responder\Http\SuccessResponse;
use Flugg\Responder\Tests\ModelWithGetResourceKey;
use Flugg\Responder\Tests\UnitTestCase;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection as IlluminateCollection;

/**
 * Unit tests for the [Flugg\Responder\Http\Normalizers\QueryBuilderNormalizer] class.
 *
 * @see \Flugg\Responder\Http\Normalizers\QueryBuilderNormalizer
 */
class QueryBuilderNormalizerTest extends UnitTestCase
{
    /**
     * Assert that [normalize] normalizes query builder to a success response value object.
     */
    public function testNormalizeMethodNormalizesQueryBuilder()
    {
        $queryBuilder = mock(Builder::class);
        $queryBuilder->allows('get')->andReturns($collection = mock(IlluminateCollection::class));
        $collection->allows(['toArray' => $data = ['foo' => 1]]);
        $normalizer = new QueryBuilderNormalizer($queryBuilder);

        $result = $normalizer->normalize();
        $resource = $result->resource();

        $this->assertInstanceOf(SuccessResponse::class, $result);
        $this->assertInstanceOf(Item::class, $resource);
        $this->assertSame(200, $result->status());
        $this->assertSame($data, $resource->toArray());
        $this->assertNull($result->resource()->key());
    }

    /**
     * Assert that [normalize] normalizes Eloquent query builder to a success response value object.
     */
    public function testNormalizeMethodNormalizesEloquentQueryBuilder()
    {
        $queryBuilder = mock(EloquentBuilder::class);
        $queryBuilder->allows('get')->andReturns($collection = mock(EloquentCollection::class));
        $collection->allows([
            'isEmpty' => false,
            'all' => [$model1 = mock(Model::class), $model2 = mock(Model::class)],
            'first' => $model1,
        ]);
        $model1->allows([
            'getTable' => $key = 'foo',
            'getRelations' => [],
            'withoutRelations' => $model1,
            'toArray' => $data1 = ['foo' => 1],
        ]);
        $model2->allows([
            'getTable' => 'bar',
            'getRelations' => [],
            'withoutRelations' => $model2,
            'toArray' => $data2 = ['bar' => 2],
        ]);
        $normalizer = new QueryBuilderNormalizer($queryBuilder);

        $result = $normalizer->normalize();
        $resource = $result->resource();

        $this->assertInstanceOf(SuccessResponse::class, $result);
        $this->assertInstanceOf(Collection::class, $resource);
        $this->assertSame(200, $result->status());
        $this->assertSame([$data1, $data2], $resource->toArray());
        $this->assertSame($key, $result->resource()->key());
        if ($resource instanceof Collection) {
            $this->assertCount(2, $resource->items());
        }
    }

    /**
     * Assert that [normalize] sets resource key to the results of [getResourceKey] on first model.
     */
    public function testNormalizeMethodSetsResourceKeyUsingMethodOnFirstModel()
    {
        $queryBuilder = mock(EloquentBuilder::class);
        $queryBuilder->allows('get')->andReturns($collection = mock(EloquentCollection::class));
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
        $normalizer = new QueryBuilderNormalizer($queryBuilder);

        $result = $normalizer->normalize();

        $this->assertSame($key, $result->resource()->key());
    }

    /**
     * Assert that [normalize] sets resource key to null when no results are found.
     */
    public function testNormalizeMethodSetsResourceKeyToNullWhenEmpty()
    {
        $queryBuilder = mock(EloquentBuilder::class);
        $queryBuilder->allows('get')->andReturns($collection = mock(EloquentCollection::class));
        $collection->allows([
            'isEmpty' => true,
            'all' => [],
        ]);
        $normalizer = new QueryBuilderNormalizer($queryBuilder);

        $result = $normalizer->normalize();

        $this->assertNull($result->resource()->key());
    }

    /**
     * Assert that [normalize] normalizes Eloquent collection with item relation.
     */
    public function testNormalizeMethodNormalizesEloquentCollectionWithItemRelation()
    {
        $queryBuilder = mock(EloquentBuilder::class);
        $queryBuilder->allows('get')->andReturns($collection = mock(EloquentCollection::class));
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
            'toArray' => $relatedData = ['bar' => 2],
        ]);
        $normalizer = new QueryBuilderNormalizer($queryBuilder);

        $result = $normalizer->normalize();
        $resource = $result->resource();

        $this->assertInstanceOf(Collection::class, $resource);
        if ($resource instanceof Collection) {
            $this->assertSame($relatedData, $resource->items()[0]->relations()['bar']->toArray());
        }
    }

    /**
     * Assert that [normalize] normalizes Eloquent collection with collection relation.
     */
    public function testNormalizeMethodNormalizesEloquentCollectionWithCollectionRelation()
    {
        $queryBuilder = mock(EloquentBuilder::class);
        $queryBuilder->allows('get')->andReturns($collection = mock(EloquentCollection::class));
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
            'toArray' => $relatedData = ['bar' => 2],
        ]);
        $normalizer = new QueryBuilderNormalizer($queryBuilder);

        $result = $normalizer->normalize();
        $resource = $result->resource();

        $this->assertInstanceOf(Collection::class, $resource);
        if ($resource instanceof Collection) {
            $this->assertSame([$relatedData], $resource->items()[0]->relations()['bar']->toArray());
        }
    }
}
