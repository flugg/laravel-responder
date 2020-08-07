<?php

namespace Flugg\Responder\Tests\Unit\Http\Normalizers;

use Flugg\Responder\Http\Normalizers\RelationNormalizer;
use Flugg\Responder\Http\SuccessResponse;
use Flugg\Responder\Tests\UnitTestCase;
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
use Illuminate\Support\Collection;

/**
 * Unit tests for the [Flugg\Responder\Http\Normalizers\RelationNormalizer] class.
 *
 * @see \Flugg\Responder\Http\Normalizers\RelationNormalizer
 */
class RelationNormalizerTest extends UnitTestCase
{
    /**
     * Class being tested.
     *
     * @var \Flugg\Responder\Http\Normalizers\RelationNormalizer
     */
    protected $normalizer;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->normalizer = new RelationNormalizer;
    }

    /**
     * Assert that [normalize] normalizes relation query builder to a success response value object.
     */
    public function testNormalizeMethodNormalizesMultipleRelations()
    {
        $classes = [BelongsToMany::class, HasMany::class, HasManyThrough::class, MorphMany::class, MorphToMany::class];

        foreach ($classes as $class) {
            $queryBuilder = mock($class);
            $queryBuilder->allows('get')->andReturns(Collection::make($data = [1, 2, 3]));

            $result = $this->normalizer->normalize($queryBuilder);

            $this->assertInstanceOf(SuccessResponse::class, $result);
            $this->assertSame($data, $result->data());
        }
    }

    /**
     * Assert that [normalize] normalizes singular relation query builder to a success response value object.
     */
    public function testNormalizeMethodNormalizesSingleRelation()
    {
        $classes = [BelongsTo::class, HasOne::class, MorphOne::class, MorphTo::class];

        foreach ($classes as $class) {
            $queryBuilder = mock($class);
            $queryBuilder->allows('first')->andReturns($model = mock(Model::class));
            $model->allows('toArray')->andReturns($data = [1, 2, 3]);

            $result = $this->normalizer->normalize($queryBuilder);

            $this->assertInstanceOf(SuccessResponse::class, $result);
            $this->assertSame($data, $result->data());
        }
    }
}
