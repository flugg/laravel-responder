<?php

namespace Flugg\Responder\Tests\Unit\Http\Normalizers;

use Flugg\Responder\Http\Normalizers\CollectionNormalizer;
use Flugg\Responder\Http\Resources\Collection;
use Flugg\Responder\Http\Resources\Item;
use Flugg\Responder\Http\SuccessResponse;
use Flugg\Responder\Tests\UnitTestCase;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection as IlluminateCollection;

/**
 * Unit tests for the [CollectionNormalizer] class.
 *
 * @see \Flugg\Responder\Http\Normalizers\CollectionNormalizer
 */
class CollectionNormalizerTest extends UnitTestCase
{
    /**
     * Assert that [normalize] normalizes collection to a success response.
     */
    public function testNormalizeMethodNormalizesCollection()
    {
        $collection = IlluminateCollection::make($data = ['foo' => 1, 'bar' => 2]);

        $result = (new CollectionNormalizer($collection))->normalize();

        $this->assertInstanceOf(SuccessResponse::class, $result);
        $this->assertSame(200, $result->status());
        $this->assertInstanceOf(Item::class, $result->resource());
        $this->assertSame($data, $result->resource()->data());
        $this->assertNull($result->resource()->key());
    }

    /**
     * Assert that [normalize] normalizes Eloquent collection to a success response.
     */
    public function testNormalizeMethodNormalizesEloquentCollection()
    {
        $collection = EloquentCollection::make([
            $this->mockModel($data1 = ['foo' => 1], $table = 'foo')->reveal(),
            $this->mockModel($data2 = ['bar' => 2], $table)->reveal(),
        ]);

        $result = (new CollectionNormalizer($collection))->normalize();

        $this->assertInstanceOf(SuccessResponse::class, $result);
        $this->assertSame(200, $result->status());
        $this->assertInstanceOf(Collection::class, $result->resource());
        $this->assertCount(2, $result->resource()->items());
        $this->assertSame($data1, $result->resource()[0]->data());
        $this->assertSame($data2, $result->resource()[1]->data());
        $this->assertSame($table, $result->resource()->key());
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

        $result = (new CollectionNormalizer($collection))->normalize();

        $this->assertSame($key, $result->resource()->key());
    }

    /**
     * Assert that [normalize] sets resource key to null when no results are found.
     */
    public function testNormalizeMethodSetsResourceKeyToNullWhenEmpty()
    {
        $collection = EloquentCollection::make([]);

        $result = (new CollectionNormalizer($collection))->normalize();

        $this->assertNull($result->resource()->key());
    }

    /**
     * Assert that [normalize] normalizes Eloquent collection with item relation.
     */
    public function testNormalizeMethodNormalizesEloquentCollectionWithItemRelation()
    {
        $collection = EloquentCollection::make([
            $this->mockModel([], 'foo', [
                'bar' => $this->mockModel($relatedData = ['foo' => 1], 'bar'),
            ])->reveal(),
            $this->mockModel([], 'baz')->reveal(),
        ]);

        $result = (new CollectionNormalizer($collection))->normalize();

        $this->assertSame($relatedData, $result->resource()[0]->relations()['bar']->data());
    }

    /**
     * Assert that [normalize] normalizes Eloquent collection with collection relation.
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

        $result = (new CollectionNormalizer($collection))->normalize();

        $this->assertSame($relatedData, $result->resource()[0]->relations()['bar'][0]->data());
    }
}
