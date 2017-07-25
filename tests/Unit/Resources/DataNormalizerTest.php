<?php

namespace Flugg\Responder\Tests\Unit\Resources;

use Flugg\Responder\Pagination\CursorFactory;
use Flugg\Responder\Pagination\CursorPaginator;
use Flugg\Responder\Pagination\PaginatorFactory;
use Flugg\Responder\Resources\DataNormalizer;
use Flugg\Responder\Resources\ResourceFactory;
use Flugg\Responder\Tests\TestCase;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use League\Fractal\Pagination\Cursor;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection as CollectionResource;
use League\Fractal\Resource\Item as ItemResource;
use League\Fractal\Resource\NullResource;
use Mockery;

/**
 * Unit tests for the [Flugg\Responder\Resources\DataNormalizerTest] class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class DataNormalizerTest extends TestCase
{
    /**
     * The data normalizer being tested.
     *
     * @var \Flugg\Responder\Resources\ResourceFactory
     */
    protected $normalizer;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->normalizer = new DataNormalizer;
    }

    /**
     *
     */
    public function testNormalizeMethodNormalizesABuilderIntoACollection()
    {
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('get')->andReturn($collection = new Collection);

        $data = $this->normalizer->normalize($builder);

        $this->assertSame($collection, $data);
    }

    /**
     *
     */
    public function testNormalizeMethodNormalizesACursorPaginatorIntoACollection()
    {
        $paginator = Mockery::mock(CursorPaginator::class);
        $paginator->shouldReceive('get')->andReturn($collection = new Collection);

        $data = $this->normalizer->normalize($paginator);

        $this->assertSame($collection, $data);
    }

    /**
     *
     */
    public function testNormalizeMethodNormalizesAPaginatorIntoACollection()
    {
        $paginator = Mockery::mock(Paginator::class);
        $paginator->shouldReceive('getCollection')->andReturn($collection = new Collection);

        $data = $this->normalizer->normalize($paginator);

        $this->assertSame($collection, $data);
    }

    /**
     *
     */
    public function testNormalizeMethodNormalizesARelationIntoACollection()
    {
        $relation = Mockery::mock(HasMany::class);
        $relation->shouldReceive('get')->andReturn($collection = new Collection);

        $data = $this->normalizer->normalize($relation);

        $this->assertSame($collection, $data);
    }

    /**
     *
     */
    public function testNormalizeMethodNormalizesASingularRelationIntoAModel()
    {
        $relation = Mockery::mock(HasOne::class);
        $relation->shouldReceive('first')->andReturn($model = Mockery::mock(Model::class));

        $data = $this->normalizer->normalize($relation);

        $this->assertSame($model, $data);
    }

    /**
     *
     */
    public function testNormalizeMethodShouldLeaveOtherDataUntouched()
    {
        $data = $this->normalizer->normalize($array = ['foo' => 123]);

        $this->assertEquals($array, $data);
    }
}