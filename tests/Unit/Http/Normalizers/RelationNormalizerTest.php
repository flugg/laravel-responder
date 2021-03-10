<?php

namespace Flugg\Responder\Tests\Unit\Http\Normalizers;

use Flugg\Responder\Http\Normalizers\RelationNormalizer;
use Flugg\Responder\Http\Resources\Collection;
use Flugg\Responder\Http\Resources\Item;
use Flugg\Responder\Http\SuccessResponse;
use Flugg\Responder\Tests\UnitTestCase;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
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
 * Unit tests for the [RelationNormalizer] class.
 *
 * @see \Flugg\Responder\Http\Normalizers\RelationNormalizer
 */
class RelationNormalizerTest extends UnitTestCase
{
    /** List of one-to-one relationship classes. */
    protected $singularRelations = [BelongsTo::class, HasOne::class, MorphOne::class, MorphTo::class];

    /** List of one-to-many and many-to-many relationship classes. */
    protected $pluralRelations = [BelongsToMany::class, HasMany::class, HasManyThrough::class, MorphMany::class, MorphToMany::class];

    /**
     * Assert that [normalize] normalizes one-to-one Eloquent relation to a success response.
     */
    public function testNormalizeMethodNormalizesSingularRelation()
    {
        foreach ($this->singularRelations as $class) {
            $model = $this->mockModel($data = ['foo' => 1], $table = 'foo');
            $relation = $this->mockSingularRelation($class, $model);

            $result = (new RelationNormalizer($relation->reveal()))->normalize();

            $this->assertInstanceOf(SuccessResponse::class, $result);
            $this->assertSame(200, $result->status());
            $this->assertInstanceOf(Item::class, $result->resource());
            $this->assertSame($data, $result->resource()->data());
            $this->assertSame($table, $result->resource()->key());
        }
    }

    /**
     * Assert that [normalize] sets resource key to the results of [getResourceKey] on model.
     */
    public function testNormalizeMethodSetsResourceKeyUsingMethodOnModel()
    {
        foreach ($this->singularRelations as $class) {
            $model = $this->mockModel([], 'foo', [], $key = 'bar');
            $relation = $this->mockSingularRelation($class, $model);

            $result = (new RelationNormalizer($relation->reveal()))->normalize();

            $this->assertSame($key, $result->resource()->key());
        }
    }

    /**
     * Assert that [normalize] normalizes one-to-one Eloquent relation with item relation.
     */
    public function testNormalizeMethodNormalizesModelWithItemRelation()
    {
        foreach ($this->singularRelations as $class) {
            $model = $this->mockModel([], 'foo', [
                'bar' => $this->mockModel($relatedData = ['foo' => 1], 'bar'),
            ]);
            $relation = $this->mockSingularRelation($class, $model);

            $result = (new RelationNormalizer($relation->reveal()))->normalize();

            $this->assertSame($relatedData, $result->resource()->relations()['bar']->data());
        }
    }

    /**
     * Assert that [normalize] normalizes one-to-one Eloquent relation with collection relation.
     */
    public function testNormalizeMethodNormalizesModelWithCollectionRelation()
    {
        foreach ($this->singularRelations as $class) {
            $model = $this->mockModel([], 'foo', [
                'bar' => EloquentCollection::make([
                    $this->mockModel($relatedData = ['foo' => 1], 'bar')->reveal(),
                ]),
            ]);
            $relation = $this->mockSingularRelation($class, $model);

            $result = (new RelationNormalizer($relation->reveal()))->normalize();

            $this->assertSame($relatedData, $result->resource()->relations()['bar'][0]->data());
        }
    }

    /**
     * Assert that [normalize] normalizes one-to-many and many-to-many Eloquent relation to a success response.
     */
    public function testNormalizeMethodNormalizesPluralRelation()
    {
        foreach ($this->pluralRelations as $class) {
            $collection = EloquentCollection::make([
                $this->mockModel($data1 = ['foo' => 1], $table = 'foo')->reveal(),
                $this->mockModel($data2 = ['bar' => 2], $table)->reveal(),
            ]);
            $relation = $this->mockPluralRelation($class, $collection);

            $result = (new RelationNormalizer($relation->reveal()))->normalize();

            $this->assertInstanceOf(SuccessResponse::class, $result);
            $this->assertSame(200, $result->status());
            $this->assertInstanceOf(Collection::class, $result->resource());
            $this->assertSame($data1, $result->resource()[0]->data());
            $this->assertSame($data2, $result->resource()[1]->data());
            $this->assertSame($table, $result->resource()->key());
            $this->assertCount(2, $result->resource()->items());
        }
    }

    /**
     * Assert that [normalize] sets resource key to the results of [getResourceKey] from first model.
     */
    public function testNormalizeMethodSetsResourceKeyUsingFirstModel()
    {
        foreach ($this->pluralRelations as $class) {
            $collection = EloquentCollection::make([
                $this->mockModel([], 'foo', [], $key = 'bar')->reveal(),
                $this->mockModel([], 'baz')->reveal(),
            ]);
            $relation = $this->mockPluralRelation($class, $collection);

            $result = (new RelationNormalizer($relation->reveal()))->normalize();

            $this->assertSame($key, $result->resource()->key());
        }
    }

    /**
     * Assert that [normalize] sets resource key to null when no results are found.
     */
    public function testNormalizeMethodSetsResourceKeyToNullWhenEmpty()
    {
        foreach ($this->pluralRelations as $class) {
            $relation = $this->mockPluralRelation($class, EloquentCollection::make());

            $result = (new RelationNormalizer($relation->reveal()))->normalize();

            $this->assertNull($result->resource()->key());
        }
    }

    /**
     * Assert that [normalize] normalizes many-to-many Eloquent relation with item relation.
     */
    public function testNormalizeMethodNormalizesEloquentCollectionWithItemRelation()
    {
        foreach ($this->pluralRelations as $class) {
            $collection = EloquentCollection::make([
                $this->mockModel([], 'foo', [
                    'bar' => $this->mockModel($relatedData = ['foo' => 1], 'bar'),
                ])->reveal(),
                $this->mockModel([], 'baz')->reveal(),
            ]);
            $relation = $this->mockPluralRelation($class, $collection);

            $result = (new RelationNormalizer($relation->reveal()))->normalize();

            $this->assertSame($relatedData, $result->resource()[0]->relations()['bar']->data());
        }
    }

    /**
     * Assert that [normalize] normalizes many-to-many Eloquent relation with collection relation.
     */
    public function testNormalizeMethodNormalizesEloquentCollectionWithCollectionRelation()
    {
        foreach ($this->pluralRelations as $class) {
            $collection = EloquentCollection::make([
                $this->mockModel([], 'foo', [
                    'bar' => EloquentCollection::make([
                        $this->mockModel($relatedData = ['foo' => 1], 'bar')->reveal(),
                    ]),
                ])->reveal(),
                $this->mockModel([], 'baz')->reveal(),
            ]);
            $relation = $this->mockPluralRelation($class, $collection);

            $result = (new RelationNormalizer($relation->reveal()))->normalize();

            $this->assertSame($relatedData, $result->resource()[0]->relations()['bar'][0]->data());
        }
    }
}
