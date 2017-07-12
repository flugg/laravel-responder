<?php

namespace Flugg\Responder\Tests\Unit\Resources;

use Flugg\Responder\Pagination\CursorFactory;
use Flugg\Responder\Pagination\CursorPaginator;
use Flugg\Responder\Pagination\PaginatorFactory;
use Flugg\Responder\Resources\DataNormalizer;
use Flugg\Responder\Resources\ResourceFactory;
use Flugg\Responder\Tests\TestCase;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use League\Fractal\Pagination\Cursor;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection as CollectionResource;
use League\Fractal\Resource\Item as ItemResource;
use League\Fractal\Resource\NullResource;
use Mockery;

/**
 * Unit tests for the [Flugg\Responder\Resources\ResourceFactory] class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class ResourceFactoryTest extends TestCase
{
    /**
     * The resource data normalier mock.
     *
     * @var \Mockery\MockInterface
     */
    protected $dataNormalizer;

    /**
     * The paginator factory mock.
     *
     * @var \Mockery\MockInterface
     */
    protected $paginatorFactory;

    /**
     * The cursor factory mock.
     *
     * @var \Mockery\MockInterface
     */
    protected $cursorFactory;

    /**
     * The resource factory.
     *
     * @var \Flugg\Responder\Resources\ResourceFactory
     */
    protected $factory;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->dataNormalizer = Mockery::mock(DataNormalizer::class);
        $this->paginatorFactory = Mockery::mock(PaginatorFactory::class);
        $this->cursorFactory = Mockery::mock(CursorFactory::class);
        $this->factory = new ResourceFactory($this->dataNormalizer, $this->paginatorFactory, $this->cursorFactory);
    }

    /**
     *
     */
    public function testMakeFromNullMethodReturnsANullResource()
    {
        $this->dataNormalizer->shouldReceive('normalize')->andReturn(null);

        $resource = $this->factory->make();

        $this->assertInstanceOf(NullResource::class, $resource);
        $this->dataNormalizer->shouldHaveReceived('normalize')->with(null);
    }

    /**
     *
     */
    public function testMakeFromModel()
    {
        $model = Mockery::mock(Model::class);
        $this->dataNormalizer->shouldReceive('normalize')->andReturn($model);

        $resource = $this->factory->make($model);

        $this->assertInstanceOf(ItemResource::class, $resource);;
        $this->assertSame($model, $resource->getData());;
        $this->dataNormalizer->shouldHaveReceived('normalize')->with($model);
    }

    /**
     *
     */
    public function testMakeFromColl()
    {
        $collection = new Collection(['foo' => 1]);
        $this->dataNormalizer->shouldReceive('normalize')->andReturn($collection);

        $resource = $this->factory->make($collection);

        $this->assertInstanceOf(CollectionResource::class, $resource);;
        $this->assertSame($collection, $resource->getData());;
        $this->dataNormalizer->shouldHaveReceived('normalize')->with($collection);
    }

    /**
     *
     */
    public function testMakeFromArr()
    {
        $array = ['foo' => 1];
        $this->dataNormalizer->shouldReceive('normalize')->andReturn($array);

        $resource = $this->factory->make($array);

        $this->assertInstanceOf(CollectionResource::class, $resource);;
        $this->assertEquals($array, $resource->getData());;
        $this->dataNormalizer->shouldHaveReceived('normalize')->with($array);
    }

    /**
     *
     */
    public function testPaginator()
    {
        $paginator = Mockery::mock(LengthAwarePaginator::class);
        $this->dataNormalizer->shouldReceive('normalize')->andReturn($collection = new Collection(['foo' => 1]));
        $this->paginatorFactory->shouldReceive('make')->andReturn($adapter = Mockery::mock(IlluminatePaginatorAdapter::class));

        $resource = $this->factory->make($paginator);

        $this->assertSame($adapter, $resource->getPaginator());;
        $this->assertSame($collection, $resource->getData());;
        $this->dataNormalizer->shouldHaveReceived('normalize')->with($paginator);
    }

    /**
     *
     */
    public function testCursor()
    {
        $paginator = Mockery::mock(CursorPaginator::class);
        $this->dataNormalizer->shouldReceive('normalize')->andReturn($collection = new Collection(['foo' => 1]));
        $this->cursorFactory->shouldReceive('make')->andReturn($cursor = Mockery::mock(Cursor::class));

        $resource = $this->factory->make($paginator);

        $this->assertSame($cursor, $resource->getCursor());;
        $this->assertSame($collection, $resource->getData());;
        $this->dataNormalizer->shouldHaveReceived('normalize')->with($paginator);
    }
}