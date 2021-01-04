<?php

namespace Flugg\Responder\Tests\Unit\Http\Normalizers;

use Flugg\Responder\Http\Normalizers\RelationNormalizer;
use Flugg\Responder\Http\Resources\Collection;
use Flugg\Responder\Http\Resources\Item;
use Flugg\Responder\Http\SuccessResponse;
use Flugg\Responder\Tests\ModelWithGetResourceKey;
use Flugg\Responder\Tests\UnitTestCase;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Unit tests for the [Flugg\Responder\Http\Normalizers\RelationNormalizer] class.
 *
 * @see \Flugg\Responder\Http\Normalizers\RelationNormalizer
 */
class RelationNormalizerTest extends UnitTestCase
{
    /** A list of one-to-one relationship classes. */
    protected $oneToOneRelations = [BelongsTo::class, HasOne::class, MorphOne::class, MorphTo::class];

    /** A list of many-to-many relationship classes. */
    protected $manyToManyRelations = [BelongsToMany::class, HasMany::class, HasManyThrough::class, MorphMany::class, MorphToMany::class];

    /**
     * Assert that [normalize] normalizes one-to-one Eloquent relation to a success response value object.
     */
    public function testNormalizeMethodNormalizesOneToOneRelation()
    {
        foreach ($this->oneToOneRelations as $class) {
            $relation = mock($class);
            $relation->allows('first')->andReturns($model = mock(Model::class));
            $model->allows([
                'getTable' => $key = 'foo',
                'getRelations' => [],
                'withoutRelations' => $model,
                'toArray' => $data = ['foo' => 123]
            ]);
            $normalizer = new RelationNormalizer($relation);

            $result = $normalizer->normalize();

            $this->assertInstanceOf(SuccessResponse::class, $result);
            $this->assertInstanceOf(Item::class, $result->resource());
            $this->assertEquals(200, $result->status());
            $this->assertSame($data, $result->resource()->toArray());
            $this->assertSame($key, $result->resource()->key());
        }
    }

    /**
     * Assert that [normalize] sets resource key to the results of [getResourceKey] on model.
     */
    public function testNormalizeMethodSetsResourceKeyUsingMethodOnModel()
    {
        foreach ($this->oneToOneRelations as $class) {
            $relation = mock($class);
            $relation->allows('first')->andReturns($model = mock(ModelWithGetResourceKey::class));
            $model->allows([
                'getResourceKey' => $key = 'foo',
                'getTable' => 'bar',
                'getRelations' => [],
                'withoutRelations' => $model,
                'toArray' => []
            ]);
            $normalizer = new RelationNormalizer($relation);

            $result = $normalizer->normalize();

            $this->assertEquals($key, $result->resource()->key());
        }
    }

    /**
     * Assert that [normalize] normalizes one-to-one Eloquent relation with item relation.
     */
    public function testNormalizeMethodNormalizesModelWithItemRelation()
    {
        foreach ($this->oneToOneRelations as $class) {
            $relation = mock($class);
            $relation->allows('first')->andReturns($model = mock(Model::class));
            $model->allows([
                'getTable' => 'foo',
                'getRelations' => ['bar' => $relatedModel = mock(Model::class)],
                'withoutRelations' => $model,
                'toArray' => []
            ]);
            $relatedModel->allows([
                'getTable' => 'bar',
                'getRelations' => [],
                'withoutRelations' => $relatedModel,
                'toArray' => $relatedData = ['bar' => 456]
            ]);
            $normalizer = new RelationNormalizer($relation);

            $result = $normalizer->normalize();
            $resource = $result->resource();

            $this->assertInstanceOf(Item::class, $resource);
            if ($resource instanceof Item) {
                $this->assertEquals($relatedData, $resource->relations()['bar']->toArray());
            }
        }
    }

    /**
     * Assert that [normalize] normalizes one-to-one Eloquent relation with collection relation.
     */
    public function testNormalizeMethodNormalizesModelWithCollectionRelation()
    {
        foreach ($this->oneToOneRelations as $class) {
            $relation = mock($class);
            $relation->allows('first')->andReturns($model = mock(Model::class));
            $model->allows([
                'getTable' => 'foo',
                'getRelations' => ['bar' => $relatedCollection = mock(EloquentCollection::class)],
                'withoutRelations' => $model,
                'toArray' => []
            ]);
            $relatedCollection->allows([
                'isEmpty' => false,
                'all' => [$relatedModel = mock(Model::class)],
                'first' => $relatedModel
            ]);
            $relatedModel->allows([
                'getTable' => 'bar',
                'getRelations' => [],
                'withoutRelations' => $relatedModel,
                'toArray' => $relatedData = ['bar' => 456]
            ]);
            $normalizer = new RelationNormalizer($relation);

            $result = $normalizer->normalize();
            $resource = $result->resource();

            $this->assertInstanceOf(Item::class, $resource);
            if ($resource instanceof Item) {
                $this->assertEquals([$relatedData], $resource->relations()['bar']->toArray());
            }
        }
    }

    /**
     * Assert that [normalize] normalizes many-to-many Eloquent relation to a success response value object.
     */
    public function testNormalizeMethodNormalizesManyToManyRelation()
    {
        foreach ($this->manyToManyRelations as $class) {
            $relation = mock($class);
            $relation->allows('get')->andReturns($collection = mock(EloquentCollection::class));
            $collection->allows([
                'isEmpty' => false,
                'all' => [$model1 = mock(Model::class), $model2 = mock(Model::class)],
                'first' => $model1
            ]);
            $model1->allows([
                'getTable' => $key = 'foo',
                'getRelations' => [],
                'withoutRelations' => $model1,
                'toArray' => $data1 = ['foo' => 123]
            ]);
            $model2->allows([
                'getTable' => 'bar',
                'getRelations' => [],
                'withoutRelations' => $model2,
                'toArray' => $data2 = ['bar' => 456]
            ]);
            $normalizer = new RelationNormalizer($relation);

            $result = $normalizer->normalize();
            $resource = $result->resource();

            $this->assertInstanceOf(SuccessResponse::class, $result);
            $this->assertInstanceOf(Collection::class, $resource);
            $this->assertEquals(200, $result->status());
            $this->assertEquals([$data1, $data2], $resource->toArray());
            $this->assertEquals($key, $result->resource()->key());
            if ($resource instanceof Collection) {
                $this->assertCount(2, $resource->items());
            }
        }
    }

    /**
     * Assert that [normalize] sets resource key to the results of [getResourceKey] on first model.
     */
    public function testNormalizeMethodSetsResourceKeyUsingMethodOnFirstModel()
    {
        foreach ($this->manyToManyRelations as $class) {
            $relation = mock($class);
            $relation->allows('get')->andReturns($collection = mock(EloquentCollection::class));
            $collection->allows([
                'isEmpty' => false,
                'all' => [$model = mock(ModelWithGetResourceKey::class)],
                'first' => $model
            ]);
            $model->allows([
                'getResourceKey' => $key = 'foo',
                'getTable' => 'bar',
                'getRelations' => [],
                'withoutRelations' => $model,
                'toArray' => []
            ]);
            $normalizer = new RelationNormalizer($relation);

            $result = $normalizer->normalize();

            $this->assertEquals($key, $result->resource()->key());
        }
    }

    /**
     * Assert that [normalize] sets resource key to null when no results are found.
     */
    public function testNormalizeMethodSetsResourceKeyToNullWhenEmpty()
    {
        foreach ($this->manyToManyRelations as $class) {
            $relation = mock($class);
            $relation->allows('get')->andReturns($collection = mock(EloquentCollection::class));
            $collection->allows([
                'isEmpty' => true,
                'all' => [],
            ]);
            $normalizer = new RelationNormalizer($relation);

            $result = $normalizer->normalize();

            $this->assertNull($result->resource()->key());
        }
    }

    /**
     * Assert that [normalize] normalizes many-to-many Eloquent relation with item relation.
     */
    public function testNormalizeMethodNormalizesEloquentCollectionWithItemRelation()
    {
        foreach ($this->manyToManyRelations as $class) {
            $relation = mock($class);
            $relation->allows('get')->andReturns($collection = mock(EloquentCollection::class));
            $collection->allows([
                'isEmpty' => false,
                'all' => [$model = mock(Model::class)],
                'first' => $model
            ]);
            $model->allows([
                'getTable' => 'foo',
                'getRelations' => ['bar' => $relatedModel = mock(Model::class)],
                'withoutRelations' => $model,
                'toArray' => []
            ]);
            $relatedModel->allows([
                'getTable' => 'bar',
                'getRelations' => [],
                'withoutRelations' => $relatedModel,
                'toArray' => $relatedData = ['bar' => 456]
            ]);
            $normalizer = new RelationNormalizer($relation);

            $result = $normalizer->normalize();
            $resource = $result->resource();

            $this->assertInstanceOf(Collection::class, $resource);
            if ($resource instanceof Collection) {
                $this->assertEquals($relatedData, $resource->items()[0]->relations()['bar']->toArray());
            }
        }
    }

    /**
     * Assert that [normalize] normalizes many-to-many Eloquent relation with collection relation.
     */
    public function testNormalizeMethodNormalizesEloquentCollectionWithCollectionRelation()
    {
        foreach ($this->manyToManyRelations as $class) {
            $relation = mock($class);
            $relation->allows('get')->andReturns($collection = mock(EloquentCollection::class));
            $collection->allows([
                'isEmpty' => false,
                'all' => [$model = mock(Model::class)],
                'first' => $model
            ]);
            $model->allows([
                'getTable' => 'foo',
                'getRelations' => ['bar' => $relatedCollection = mock(EloquentCollection::class)],
                'withoutRelations' => $model,
                'toArray' => []
            ]);
            $relatedCollection->allows([
                'isEmpty' => false,
                'all' => [$relatedModel = mock(Model::class)],
                'first' => $relatedModel
            ]);
            $relatedModel->allows([
                'getTable' => 'bar',
                'getRelations' => [],
                'withoutRelations' => $relatedModel,
                'toArray' => $relatedData = ['bar' => 456]
            ]);
            $normalizer = new RelationNormalizer($relation);

            $result = $normalizer->normalize();
            $resource = $result->resource();

            $this->assertInstanceOf(Collection::class, $resource);
            if ($resource instanceof Collection) {
                $this->assertEquals([$relatedData], $resource->items()[0]->relations()['bar']->toArray());
            }
        }
    }
}
