<?php

namespace Flugg\Responder\Tests\Unit;

use Flugg\Responder\Contracts\TransformFactory;
use Flugg\Responder\Exceptions\InvalidSerializerException;
use Flugg\Responder\Pagination\CursorPaginator;
use Flugg\Responder\Pagination\PaginatorFactory;
use Flugg\Responder\Resources\ResourceFactory;
use Flugg\Responder\Serializers\SuccessSerializer;
use Flugg\Responder\Tests\TestCase;
use Flugg\Responder\TransformBuilder;
use Flugg\Responder\Transformers\Transformer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use League\Fractal\Pagination\Cursor;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\NullResource;
use League\Fractal\Serializer\JsonApiSerializer;
use Mockery;
use stdClass;

/**
 * Unit tests for the [Flugg\Responder\TransformBuilderTest] class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class TransformBuilderTest extends TestCase
{
    /**
     * Mock of a resource factory class.
     *
     * @var \Mockery\MockInterface
     */
    protected $resourceFactory;

    /**
     * Mock of a transform factory class.
     *
     * @var \Mockery\MockInterface
     */
    protected $transformFactory;

    /**
     * Mock of a paginator factory class.
     *
     * @var \Mockery\MockInterface
     */
    protected $paginatorFactory;

    /**
     * Mock of a default resource.
     *
     * @var \Mockery\MockInterface
     */
    protected $resource;

    /**
     * Mock of a default success serializer.
     *
     * @var \Mockery\MockInterface
     */
    protected $serializer;

    /**
     * The transform builder class being tested.
     *
     * @var \Flugg\Responder\TransformBuilder
     */
    protected $builder;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->resourceFactory = Mockery::mock(ResourceFactory::class);
        $this->resourceFactory->shouldReceive('make')->andReturn($this->resource = Mockery::mock(NullResource::class));
        $this->resource->shouldReceive('getData')->andReturnNull()->byDefault();
        $this->resource->shouldReceive('getTransformer')->andReturnNull()->byDefault();
        $this->resource->shouldReceive('setMeta')->andReturnSelf()->byDefault();
        $this->resource->shouldReceive('setCursor')->andReturnSelf()->byDefault();
        $this->resource->shouldReceive('setPaginator')->andReturnSelf()->byDefault();

        $this->transformFactory = Mockery::mock(TransformFactory::class);
        $this->paginatorFactory = Mockery::mock(PaginatorFactory::class);

        $this->builder = new TransformBuilder($this->resourceFactory, $this->transformFactory, $this->paginatorFactory);
        $this->builder->serializer($this->serializer = Mockery::mock(SuccessSerializer::class));
    }

    /**
     *
     */
    public function testResourceMethodMakesAResource()
    {
        [$data, $transformer, $resourceKey] = [['foo' => 1], $this->mockTransformer(), 'foo'];

        $result = $this->builder->resource($data, $transformer, $resourceKey);

        $this->assertSame($this->builder, $result);
        $this->resourceFactory->shouldHaveReceived('make')->with($data, $transformer, $resourceKey)->once();
    }

    /**
     *
     */
    public function testResourceSetsCursorIfDataIsACursorPaginator()
    {
        $data = Mockery::mock(CursorPaginator::class);
        $cursor = Mockery::mock(Cursor::class);
        $this->paginatorFactory->shouldReceive('makeCursor')->andReturn($cursor);

        $this->builder->resource($data);

        $this->resource->shouldHaveReceived('setCursor')->with($cursor)->once();
    }

    /**
     *
     */
    public function testResourceSetsPaginatorIfDataIsAPaginator()
    {
        $data = Mockery::mock(LengthAwarePaginator::class);
        $paginator = Mockery::mock(IlluminatePaginatorAdapter::class);
        $this->paginatorFactory->shouldReceive('make')->andReturn($paginator);

        $this->builder->resource($data);

        $this->resource->shouldHaveReceived('setPaginator')->with($paginator)->once();
    }

    /**
     *
     */
    public function testCursorMethodAllowsToManuallySetCursor()
    {
        $cursor = Mockery::mock(Cursor::class);
        $this->paginatorFactory->shouldReceive('makeCursor')->andReturn($cursor);

        $this->builder->cursor($cursor);

        $this->resource->shouldHaveReceived('setCursor')->with($cursor)->once();
    }

    /**
     *
     */
    public function testPaginatorMethodAllowsToManuallySetPaginator()
    {
        $paginator = Mockery::mock(IlluminatePaginatorAdapter::class);
        $this->paginatorFactory->shouldReceive('make')->andReturn($paginator);

        $this->builder->paginator($paginator);

        $this->resource->shouldHaveReceived('setPaginator')->with($paginator)->once();
    }

    /**
     *
     */
    public function testMetaMethodAddsMetaToTheResourceBuilder()
    {
        $result = $this->builder->meta($meta = ['foo' => 1]);

        $this->assertSame($this->builder, $result);
        $this->resource->shouldHaveReceived('setMeta')->with($meta)->once();
    }

    /**
     *
     */
    public function testTransformMethodShouldUseTransformFactory()
    {
        $this->transformFactory->shouldReceive('make')->andReturn($data = ['foo' => 123]);

        $result = $this->builder->transform();

        $this->assertEquals($data, $result);
        $this->transformFactory->shouldHaveReceived('make')->with($this->resource, $this->serializer, [
            'includes' => [],
            'excludes' => [],
            'fields' => [],
        ])->once();
    }

    /**
     *
     */
    public function testSerializerMethodChangesTheSerializerSentToTheTransformFactory()
    {
        $this->transformFactory->shouldReceive('make')->andReturn([]);

        $this->builder->serializer($serializer = new JsonApiSerializer)->transform();

        $this->transformFactory->shouldHaveReceived('make')->with($this->resource, $serializer, [
            'includes' => [],
            'excludes' => [],
            'fields' => [],
        ])->once();
    }

    /**
     *
     */
    public function testSerializerMethodAcceptsClassNameString()
    {
        $this->transformFactory->shouldReceive('make')->andReturn([]);

        $this->builder->serializer($serializer = JsonApiSerializer::class)->transform();

        $this->transformFactory->shouldHaveReceived('make')->with($this->resource, $serializer, [
            'includes' => [],
            'excludes' => [],
            'fields' => [],
        ])->once();
    }

    /**
     *
     */
    public function testSerializerMethodThrowsExceptionWhenGivenInvalidSerializer()
    {
        $this->expectException(InvalidSerializerException::class);

        $this->builder->serializer($serializer = stdClass::class)->transform();
    }

    /**
     *
     */
    public function testWithMethodSetsIncludedRelationsSentToFactory()
    {
        $this->transformFactory->shouldReceive('make')->andReturn([]);

        $this->builder->with($relations = ['foo', 'bar'])->transform();

        $this->transformFactory->shouldHaveReceived('make')->with($this->resource, $this->serializer, [
            'includes' => $relations,
            'excludes' => [],
            'fields' => [],
        ])->once();
    }

    /**
     *
     */
    public function testWithMethodCanBeCalledMultipleTimesAndAllowsString()
    {
        $this->transformFactory->shouldReceive('make')->andReturn([]);

        $this->builder->with('foo')->with('bar', 'baz')->transform();

        $this->transformFactory->shouldHaveReceived('make')->with($this->resource, $this->serializer, [
            'includes' => ['foo', 'bar', 'baz'],
            'excludes' => [],
            'fields' => [],
        ])->once();
    }

    /**
     *
     */
    public function testWithoutMethodSetsExcludedRelationsSentToFactory()
    {
        $this->transformFactory->shouldReceive('make')->andReturn([]);

        $this->builder->without($relations = ['foo', 'bar'])->transform();

        $this->transformFactory->shouldHaveReceived('make')->with($this->resource, $this->serializer, [
            'includes' => [],
            'excludes' => $relations,
            'fields' => [],
        ])->once();
    }

    /**
     *
     */
    public function testWithoutMethodCanBeCalledMultipleTimesAndAllowsStrings()
    {
        $this->transformFactory->shouldReceive('make')->andReturn([]);

        $this->builder->without('foo')->without('bar', 'baz')->transform();

        $this->transformFactory->shouldHaveReceived('make')->with($this->resource, $this->serializer, [
            'includes' => [],
            'excludes' => ['foo', 'bar', 'baz'],
            'fields' => [],
        ])->once();
    }

    /**
     *
     */
    public function testItEagerLoadsData()
    {
        $this->transformFactory->shouldReceive('make')->andReturn([]);
        $this->resource->shouldReceive('getData')->andReturn($model = Mockery::mock(Model::class));
        $model->shouldReceive('load')->andReturnSelf();
        $this->resource->shouldReceive('getTransformer')->andReturn($transformer = Mockery::mock(Transformer::class));
        $transformer->shouldReceive('extractDefaultRelations')->andReturn($default = ['baz']);

        $this->builder->with($relations = ['foo' => function () { }, 'bar'])->transform();

        $model->shouldHaveReceived('load')->with(array_merge($relations, $default))->once();
        $this->transformFactory->shouldHaveReceived('make')->with($this->resource, $this->serializer, [
            'includes' => ['foo', 'bar', 'baz'],
            'excludes' => [],
            'fields' => [],
        ])->once();
    }

    /**
     *
     */
    public function testOnlyMethodSetsFilteredFieldsSentToFactory()
    {
        $this->transformFactory->shouldReceive('make')->andReturn([]);

        $this->builder->only($fields = ['foo', 'bar'])->transform();

        $this->transformFactory->shouldHaveReceived('make')->with($this->resource, $this->serializer, [
            'includes' => [],
            'excludes' => [],
            'fields' => $fields,
        ])->once();
    }

    /**
     *
     */
    public function testOnlyMethodCanBeCalledMultipleTimesAndAllowsString()
    {
        $this->transformFactory->shouldReceive('make')->andReturn([]);

        $this->builder->only('foo')->only('bar', 'baz')->transform();

        $this->transformFactory->shouldHaveReceived('make')->with($this->resource, $this->serializer, [
            'includes' => [],
            'excludes' => [],
            'fields' => ['foo', 'bar', 'baz'],
        ])->once();
    }
}