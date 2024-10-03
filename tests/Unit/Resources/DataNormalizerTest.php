<?php

namespace Flugg\Responder\Tests\Unit\Resources;

use Flugg\Responder\Pagination\CursorFactory;
use Flugg\Responder\Pagination\CursorPaginator;
use Flugg\Responder\Resources\DataNormalizer;
use Flugg\Responder\Tests\TestCase;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Mockery;

/**
 * Unit tests for the [Flugg\Responder\Resources\DataNormalizerTest] class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
final class DataNormalizerTest extends TestCase
{
    /**
     * The [DataNormalizer] class being tested.
     *
     * @var \Flugg\Responder\Resources\ResourceFactory
     */
    protected $normalizer;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->normalizer = new DataNormalizer;
    }

    /**
     * Assert the the [normalize] method converts query builder instances to collections.
     */
    public function testNormalizeMethodShouldConvertQueryBuildersToCollections(): void
    {
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('get')->andReturn($collection = new Collection);

        $data = $this->normalizer->normalize($builder);

        $this->assertSame($collection, $data);
    }

    /**
     * Assert the the [normalize] method converts paginator instances to collections.
     */
    public function testNormalizeMethodShouldConvertPaginatorsToCollections(): void
    {
        $paginator = Mockery::mock(Paginator::class);
        $paginator->shouldReceive('getCollection')->andReturn($collection = new Collection);

        $data = $this->normalizer->normalize($paginator);

        $this->assertSame($collection, $data);
    }

    /**
     * Assert the the [normalize] method converts cursor paginator instances to collections.
     */
    public function testNormalizeMethodShouldConvertCursorPaginatorsToCollections(): void
    {
        $paginator = Mockery::mock(CursorPaginator::class);
        $paginator->shouldReceive('get')->andReturn($collection = new Collection);

        $data = $this->normalizer->normalize($paginator);

        $this->assertSame($collection, $data);
    }

    /**
     * Assert the the [normalize] method converts relationship instances to collections.
     */
    public function testNormalizeMethodShouldConvertRelationsToCollections(): void
    {
        $relation = Mockery::mock(HasMany::class);
        $relation->shouldReceive('get')->andReturn($collection = new Collection);

        $data = $this->normalizer->normalize($relation);

        $this->assertSame($collection, $data);
    }

    /**
     * Assert the the [normalize] method converts singular relationship instances to models.
     */
    public function testNormalizeMethodShouldConvertSingularRelationsToModels(): void
    {
        $relation = Mockery::mock(HasOne::class);
        $relation->shouldReceive('first')->andReturn($model = Mockery::mock(Model::class));

        $data = $this->normalizer->normalize($relation);

        $this->assertSame($model, $data);
    }

    /**
     * Assert that the [normalize] methods leaves other data types untouched.
     */
    public function testNormalizeMethodShouldReturnDataDirectlyIfUnknownType(): void
    {
        $data = $this->normalizer->normalize($array = ['foo' => 123]);

        $this->assertEquals($array, $data);
    }
}