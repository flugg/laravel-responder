<?php

namespace Flugg\Responder\Tests\Unit\Http\Normalizers;

use Flugg\Responder\Http\Normalizers\QueryBuilderNormalizer;
use Flugg\Responder\Http\Resources\Collection;
use Flugg\Responder\Http\Resources\Item;
use Flugg\Responder\Http\SuccessResponse;
use Flugg\Responder\Tests\UnitTestCase;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection as IlluminateCollection;

/**
 * Unit tests for the [QueryBuilderNormalizer] class.
 *
 * @see \Flugg\Responder\Http\Normalizers\QueryBuilderNormalizer
 */
class QueryBuilderNormalizerTest extends UnitTestCase
{
    /**
     * Assert that [normalize] normalizes query builder to a success response.
     */
    public function testNormalizeMethodNormalizesQueryBuilder()
    {
        $collection = IlluminateCollection::make($data = ['foo' => 1, 'bar' => 2]);
        $queryBuilder = $this->mock(Builder::class);
        $queryBuilder->get()->willReturn($collection);

        $result = (new QueryBuilderNormalizer($queryBuilder->reveal()))->normalize();

        $this->assertInstanceOf(SuccessResponse::class, $result);
        $this->assertSame(200, $result->status());
        $this->assertInstanceOf(Item::class, $result->resource());
        $this->assertSame($data, $result->resource()->data());
        $this->assertNull($result->resource()->key());
    }

    /**
     * Assert that [normalize] normalizes Eloquent query builder to a success response.
     */
    public function testNormalizeMethodNormalizesEloquentQueryBuilder()
    {
        $collection = EloquentCollection::make([
            $this->mockModel($data1 = ['foo' => 1], $table = 'foo')->reveal(),
            $this->mockModel($data2 = ['bar' => 2], $table)->reveal(),
        ]);
        $queryBuilder = $this->mock(EloquentBuilder::class);
        $queryBuilder->get()->willReturn($collection);

        $result = (new QueryBuilderNormalizer($queryBuilder->reveal()))->normalize();

        $this->assertInstanceOf(SuccessResponse::class, $result);
        $this->assertSame(200, $result->status());
        $this->assertInstanceOf(Collection::class, $result->resource());
        $this->assertSame($data1, $result->resource()[0]->data());
        $this->assertSame($data2, $result->resource()[1]->data());
        $this->assertSame($table, $result->resource()->key());
        $this->assertCount(2, $result->resource()->items());
    }

    /**
     * Assert that [normalize] sets resource key to the results of [getResourceKey] from first model.
     */
    public function testNormalizeMethodSetsResourceKeyUsingFirstModel()
    {
        $collection = EloquentCollection::make([
            $this->mockModel([], 'foo', [], $key = 'bar')->reveal(),
            $this->mockModel([], 'baz')->reveal(),
        ]);
        $queryBuilder = $this->mock(EloquentBuilder::class);
        $queryBuilder->get()->willReturn($collection);

        $result = (new QueryBuilderNormalizer($queryBuilder->reveal()))->normalize();

        $this->assertSame($key, $result->resource()->key());
    }

    /**
     * Assert that [normalize] sets resource key to null when no results are found.
     */
    public function testNormalizeMethodSetsResourceKeyToNullWhenEmpty()
    {
        $queryBuilder = $this->mock(EloquentBuilder::class);
        $queryBuilder->get()->willReturn(EloquentCollection::make());

        $result = (new QueryBuilderNormalizer($queryBuilder->reveal()))->normalize();

        $this->assertNull($result->resource()->key());
    }

    /**
     * Assert that [normalize] normalizes Eloquent query builder with item relation.
     */
    public function testNormalizeMethodNormalizesEloquentCollectionWithItemRelation()
    {
        $collection = EloquentCollection::make([
            $this->mockModel([], 'foo', [
                'bar' => $this->mockModel($relatedData = ['foo' => 1], 'bar'),
            ])->reveal(),
            $this->mockModel([], 'baz')->reveal(),
        ]);
        $queryBuilder = $this->mock(EloquentBuilder::class);
        $queryBuilder->get()->willReturn($collection);

        $result = (new QueryBuilderNormalizer($queryBuilder->reveal()))->normalize();

        $this->assertSame($relatedData, $result->resource()[0]->relations()['bar']->data());
    }

    /**
     * Assert that [normalize] normalizes Eloquent query builder with collection relation.
     */
    public function testNormalizeMethodNormalizesEloquentCollectionWithCollectionRelation()
    {
        $collection = EloquentCollection::make([
            $this->mockModel([], 'foo', [
                'bar' => EloquentCollection::make([
                    $this->mockModel($relatedData = ['foo' => 1], 'bar')->reveal(),
                ]),
            ])->reveal(),
            $this->mockModel([], 'baz')->reveal(),
        ]);
        $queryBuilder = $this->mock(EloquentBuilder::class);
        $queryBuilder->get()->willReturn($collection);

        $result = (new QueryBuilderNormalizer($queryBuilder->reveal()))->normalize();

        $this->assertSame($relatedData, $result->resource()[0]->relations()['bar'][0]->data());
    }
}
