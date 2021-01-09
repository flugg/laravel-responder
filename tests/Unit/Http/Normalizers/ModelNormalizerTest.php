<?php

namespace Flugg\Responder\Tests\Unit\Http\Normalizers;

use Flugg\Responder\Http\Normalizers\ModelNormalizer;
use Flugg\Responder\Http\Resources\Item;
use Flugg\Responder\Http\SuccessResponse;
use Flugg\Responder\Tests\ModelWithGetResourceKey;
use Flugg\Responder\Tests\UnitTestCase;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;

/**
 * Unit tests for the [Flugg\Responder\Http\Normalizers\ModelNormalizer] class.
 *
 * @see \Flugg\Responder\Http\Normalizers\ModelNormalizer
 */
class ModelNormalizerTest extends UnitTestCase
{
    /**
     * Assert that [normalize] normalizes Eloquent model to a success response value object.
     */
    public function testNormalizeMethodNormalizesModel()
    {
        $model = mock(Model::class);
        $model->allows([
            'getTable' => $key = 'foo',
            'getRelations' => [],
            'withoutRelations' => $model,
            'toArray' => $data = ['foo' => 1],
        ]);
        $normalizer = new ModelNormalizer($model);

        $result = $normalizer->normalize();

        $this->assertInstanceOf(SuccessResponse::class, $result);
        $this->assertInstanceOf(Item::class, $result->resource());
        $this->assertEquals(200, $result->status());
        $this->assertSame($data, $result->resource()->toArray());
        $this->assertSame($key, $result->resource()->key());
    }

    /**
     * Assert that [normalize] sets 201 status code for recently created models.
     */
    public function testNormalizeMethodSetsCreatedStatusCodeForRecentlyCreatedModels()
    {
        $model = mock(Model::class);
        $model->allows([
            'getTable' => 'foo',
            'getRelations' => [],
            'withoutRelations' => $model,
            'toArray' => [],
        ]);
        $model->wasRecentlyCreated = 201;
        $normalizer = new ModelNormalizer($model);

        $result = $normalizer->normalize();

        $this->assertEquals(201, $result->status());
    }

    /**
     * Assert that [normalize] sets resource key to the results of [getResourceKey] on model.
     */
    public function testNormalizeMethodSetsResourceKeyUsingMethodOnModel()
    {
        $model = mock(ModelWithGetResourceKey::class);
        $model->allows([
            'getResourceKey' => $key = 'foo',
            'getTable' => 'bar',
            'getRelations' => [],
            'withoutRelations' => $model,
            'toArray' => [],
        ]);
        $normalizer = new ModelNormalizer($model);

        $result = $normalizer->normalize();

        $this->assertEquals($key, $result->resource()->key());
    }

    /**
     * Assert that [normalize] normalizes Eloquent model with item relation.
     */
    public function testNormalizeMethodNormalizesModelWithItemRelation()
    {
        $model = mock(Model::class);
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
        $normalizer = new ModelNormalizer($model);

        $result = $normalizer->normalize();
        $resource = $result->resource();

        $this->assertInstanceOf(Item::class, $resource);
        if ($resource instanceof Item) {
            $this->assertEquals($relatedData, $resource->relations()['bar']->toArray());
        }
    }

    /**
     * Assert that [normalize] normalizes Eloquent model with collection relation.
     */
    public function testNormalizeMethodNormalizesModelWithCollectionRelation()
    {
        $model = mock(Model::class);
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
        $normalizer = new ModelNormalizer($model);

        $result = $normalizer->normalize();
        $resource = $result->resource();

        $this->assertInstanceOf(Item::class, $resource);
        if ($resource instanceof Item) {
            $this->assertEquals([$relatedData], $resource->relations()['bar']->toArray());
        }
    }
}
