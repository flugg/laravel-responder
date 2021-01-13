<?php

namespace Flugg\Responder\Tests\Unit\Http\Normalizers;

use Flugg\Responder\Http\Normalizers\ModelNormalizer;
use Flugg\Responder\Http\Resources\Item;
use Flugg\Responder\Http\SuccessResponse;
use Flugg\Responder\Tests\UnitTestCase;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

/**
 * Unit tests for the [ModelNormalizer] class.
 *
 * @see \Flugg\Responder\Http\Normalizers\ModelNormalizer
 */
class ModelNormalizerTest extends UnitTestCase
{
    /**
     * Assert that [normalize] normalizes Eloquent model to a success response.
     */
    public function testNormalizeMethodNormalizesModel()
    {
        $model = $this->mockModel($data = ['foo' => 1], $table = 'foo');

        $result = (new ModelNormalizer($model->reveal()))->normalize();

        $this->assertInstanceOf(SuccessResponse::class, $result);
        $this->assertSame(200, $result->status());
        $this->assertInstanceOf(Item::class, $result->resource());
        $this->assertSame($data, $result->resource()->data());
        $this->assertSame($table, $result->resource()->key());
    }

    /**
     * Assert that [normalize] sets 201 status code for recently created models.
     */
    public function testNormalizeMethodSetsCreatedStatusCodeForRecentlyCreatedModels()
    {
        $model = $this->mockModel([], 'foo');
        $model->wasRecentlyCreated = true;

        $result = (new ModelNormalizer($model->reveal()))->normalize();

        $this->assertSame(201, $result->status());
    }

    /**
     * Assert that [normalize] sets resource key to the results of [getResourceKey] on model.
     */
    public function testNormalizeMethodSetsResourceKeyUsingMethodOnModel()
    {
        $model = $this->mockModel([], 'foo', [], $key = 'bar');

        $result = (new ModelNormalizer($model->reveal()))->normalize();

        $this->assertSame($key, $result->resource()->key());
    }

    /**
     * Assert that [normalize] normalizes Eloquent model with item relation.
     */
    public function testNormalizeMethodNormalizesModelWithItemRelation()
    {
        $model = $this->mockModel([], 'foo', [
            'bar' => $this->mockModel($relatedData = ['foo' => 1], 'bar'),
        ]);

        $result = (new ModelNormalizer($model->reveal()))->normalize();

        $this->assertSame($relatedData, $result->resource()->relations()['bar']->data());
    }

    /**
     * Assert that [normalize] normalizes Eloquent model with collection relation.
     */
    public function testNormalizeMethodNormalizesModelWithCollectionRelation()
    {
        $model = $this->mockModel([], 'foo', [
            'bar' => EloquentCollection::make([
                $this->mockModel($relatedData = ['foo' => 1], 'bar')->reveal(),
            ]),
        ]);

        $result = (new ModelNormalizer($model->reveal()))->normalize();

        $this->assertSame($relatedData, $result->resource()->relations()['bar'][0]->data());
    }
}
