<?php

namespace Flugg\Responder\Tests\Unit\Http\Normalizers;

use Flugg\Responder\Adapters\IlluminatePaginatorAdapter;
use Flugg\Responder\Http\Normalizers\PaginatorNormalizer;
use Flugg\Responder\Http\Resources\Collection;
use Flugg\Responder\Http\SuccessResponse;
use Flugg\Responder\Tests\UnitTestCase;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection as IlluminateCollection;

/**
 * Unit tests for the [PaginatorNormalizer] class.
 *
 * @see \Flugg\Responder\Http\Normalizers\PaginatorNormalizer
 */
class PaginatorNormalizerTest extends UnitTestCase
{
    /**
     * Assert that [normalize] normalizes paginator to a success response.
     */
    public function testNormalizeMethodNormalizesPaginator()
    {
        $collection = IlluminateCollection::make([
            $this->mockModel($data1 = ['foo' => 1], $table = 'foo')->reveal(),
            $this->mockModel($data2 = ['bar' => 2], $table)->reveal(),
        ]);
        $paginator = $this->mock(LengthAwarePaginator::class);
        $paginator->getCollection()->willReturn($collection);

        $result = (new PaginatorNormalizer($paginator->reveal()))->normalize();

        $this->assertInstanceOf(SuccessResponse::class, $result);
        $this->assertInstanceOf(IlluminatePaginatorAdapter::class, $result->paginator());
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
        $collection = IlluminateCollection::make([
            $this->mockModel([], 'foo', [], $key = 'bar')->reveal(),
            $this->mockModel([], 'baz')->reveal(),
        ]);
        $paginator = $this->mock(LengthAwarePaginator::class);
        $paginator->getCollection()->willReturn($collection);

        $result = (new PaginatorNormalizer($paginator->reveal()))->normalize();

        $this->assertSame($key, $result->resource()->key());
    }

    /**
     * Assert that [normalize] sets resource key to null when no results are found.
     */
    public function testNormalizeMethodSetsResourceKeyToNullWhenNoResults()
    {
        $paginator = $this->mock(LengthAwarePaginator::class);
        $paginator->getCollection()->willReturn(IlluminateCollection::make());

        $result = (new PaginatorNormalizer($paginator->reveal()))->normalize();

        $this->assertNull($result->resource()->key());
    }

    /**
     * Assert that [normalize] normalizes paginator with item relation.
     */
    public function testNormalizeMethodNormalizesEloquentCollectionWithItemRelation()
    {
        $collection = IlluminateCollection::make([
            $this->mockModel([], 'foo', [
                'bar' => $this->mockModel($relatedData = ['foo' => 1], 'bar'),
            ])->reveal(),
            $this->mockModel([], 'baz')->reveal(),
        ]);
        $paginator = $this->mock(LengthAwarePaginator::class);
        $paginator->getCollection()->willReturn($collection);

        $result = (new PaginatorNormalizer($paginator->reveal()))->normalize();

        $this->assertSame($relatedData, $result->resource()[0]->relations()['bar']->data());
    }

    /**
     * Assert that [normalize] normalizes paginator with collection relation.
     */
    public function testNormalizeMethodNormalizesEloquentCollectionWithCollectionRelation()
    {
        $collection = IlluminateCollection::make([
            $this->mockModel([], 'foo', [
                'bar' => EloquentCollection::make([
                    $this->mockModel($relatedData = ['foo' => 1], 'bar')->reveal(),
                ]),
            ])->reveal(),
            $this->mockModel([], 'baz')->reveal(),
        ]);
        $paginator = $this->mock(LengthAwarePaginator::class);
        $paginator->getCollection()->willReturn($collection);

        $result = (new PaginatorNormalizer($paginator->reveal()))->normalize();

        $this->assertSame($relatedData, $result->resource()[0]->relations()['bar'][0]->data());
    }
}
