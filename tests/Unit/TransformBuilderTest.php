<?php

namespace Flugg\Responder\Tests\Unit;

use Flugg\Responder\Contracts\TransformFactory;
use Flugg\Responder\Exceptions\InvalidSuccessSerializerException;
use Flugg\Responder\Pagination\CursorPaginator;
use Flugg\Responder\Pagination\PaginatorFactory;
use Flugg\Responder\Resources\ResourceFactory;
use Flugg\Responder\Serializers\SuccessSerializer;
use Flugg\Responder\Tests\TestCase;
use Flugg\Responder\TransformBuilder;
use Flugg\Responder\Transformers\Transformer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use League\Fractal\Pagination\Cursor;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
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
final class TransformBuilderTest extends TestCase
{
    /**
     * A mock of a [ResourceFactory] class.
     *
     * @var \Mockery\MockInterface
     */
    protected $resourceFactory;

    /**
     * A mock of a [TransformFactory] class.
     *
     * @var \Mockery\MockInterface
     */
    protected $transformFactory;

    /**
     * A mock of a [PaginatorFactory] class.
     *
     * @var \Mockery\MockInterface
     */
    protected $paginatorFactory;

    /**
     * A mock of a [ResourceInterface] class.
     *
     * @var \Mockery\MockInterface
     */
    protected $resource;

    /**
     * A mock of a [SerializerAbstract] class.
     *
     * @var \Mockery\MockInterface
     */
    protected $serializer;

    /**
     * The [TransformBuilder] class being tested.
     *
     * @var \Flugg\Responder\TransformBuilder
     */
    protected $builder;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->resourceFactory = Mockery::mock(ResourceFactory::class);
        $this->transformFactory = Mockery::mock(TransformFactory::class);
        $this->paginatorFactory = Mockery::mock(PaginatorFactory::class);
        $this->resourceFactory->shouldReceive('make')->andReturn($this->resource = $this->mockResource());
        $this->builder = new TransformBuilder($this->resourceFactory, $this->transformFactory, $this->paginatorFactory);
        $this->builder->serializer($this->serializer = Mockery::mock(SuccessSerializer::class));
    }

    /**
     * Assert that the [resource] method uses the [ResourceFactory] to create resources.
     */
    public function testResourceMethodUsesResourceFactory(): void
    {
        $result = $this->builder->resource($data = ['foo' => 1], $transformer = $this->mockTransformer(), $resourceKey = 'foo');

        $this->assertSame($this->builder, $result);
        $this->resourceFactory->shouldHaveReceived('make')->with($data, $transformer, $resourceKey)->once();
    }

    /**
     * Assert that the [resource] method sets cursor on the resource if data is an instance
     * of [CursorPaginator].
     */
    public function testResourceMethodSetsCursorOnResource(): void
    {
        $cursor = Mockery::mock(Cursor::class);
        $this->paginatorFactory->shouldReceive('makeCursor')->andReturn($cursor);

        $this->builder->resource($data = Mockery::mock(CursorPaginator::class));

        $this->resource->shouldHaveReceived('setCursor')->with($cursor)->once();
    }

    /**
     * Assert that the [resource] method sets paginator on the resource if data is an instance
     * of [LengthAwarePaginator].
     */
    public function testResourceMethodSetsPagintorOnResource(): void
    {
        $paginator = Mockery::mock(IlluminatePaginatorAdapter::class);
        $this->paginatorFactory->shouldReceive('make')->andReturn($paginator);

        $this->builder->resource($data = Mockery::mock(LengthAwarePaginator::class));

        $this->resource->shouldHaveReceived('setPaginator')->with($paginator)->once();
    }

    /**
     * Assert that the [cursor] method allows manually setting cursor on resource.
     */
    public function testCursorMethodSetsCursorsOnResource(): void
    {
        $cursor = Mockery::mock(Cursor::class);
        $this->paginatorFactory->shouldReceive('makeCursor')->andReturn($cursor);

        $this->builder->resource()->cursor($cursor);

        $this->resource->shouldHaveReceived('setCursor')->with($cursor)->once();
    }

    /**
     * Assert that the [paginator] method allows manually setting paginator on resource.
     */
    public function testPaginatorMethodSetsPaginatorsOnResource(): void
    {
        $paginator = Mockery::mock(IlluminatePaginatorAdapter::class);
        $this->paginatorFactory->shouldReceive('make')->andReturn($paginator);

        $this->builder->resource()->paginator($paginator);

        $this->resource->shouldHaveReceived('setPaginator')->with($paginator)->once();
    }

    /**
     * Assert that the [meta] method adds meta data to the resource.
     */
    public function testMetaMethodAddsMetaDataToResource(): void
    {
        $result = $this->builder->resource()->meta($meta = ['foo' => 1]);

        $this->assertSame($this->builder, $result);
        $this->resource->shouldHaveReceived('setMeta')->with($meta)->once();
    }

    /**
     * Assert that the [transform] method transforms data using [TransformFactory].
     */
    public function testTransformMethodUsesTransformFactoryToTransformData(): void
    {
        $this->transformFactory->shouldReceive('make')->andReturn($data = ['foo' => 123]);

        $result = $this->builder->resource()->transform();

        $this->assertEquals($data, $result);
        $this->transformFactory->shouldHaveReceived('make')->with($this->resource, $this->serializer, [
            'includes' => [],
            'excludes' => [],
            'fieldsets' => [],
        ])->once();
    }

    /**
     * Assert that the [serializer] method sets the serializer that is sent to the
     * [TransformFactory].
     */
    public function testSerializerMethodSetsSerializerSentToTransformFactory(): void
    {
        $this->transformFactory->shouldReceive('make')->andReturn([]);

        $this->builder->resource()->serializer($serializer = new JsonApiSerializer())->transform();

        $this->transformFactory->shouldHaveReceived('make')->with($this->resource, $serializer, [
            'includes' => [],
            'excludes' => [],
            'fieldsets' => [],
        ])->once();
    }

    /**
     * Assert that the [serializer] method allows class name strings.
     */
    public function testSerializerMethodAllowsClassNameStrings(): void
    {
        $this->transformFactory->shouldReceive('make')->andReturn([]);

        $this->builder->resource()->serializer($serializer = JsonApiSerializer::class)->transform();

        $this->transformFactory->shouldHaveReceived('make')->with($this->resource, $serializer, [
            'includes' => [],
            'excludes' => [],
            'fieldsets' => [],
        ])->once();
    }

    /**
     * Assert that the [serializer] method throws [InvalidSuccessSerializerException] exception when
     * given an invalid serializer.
     */
    public function testSerializerMethodThrowsExceptionWhenGivenInvalidSerializer(): void
    {
        $this->expectException(InvalidSuccessSerializerException::class);

        $this->builder->serializer($serializer = stdClass::class);
    }

    /**
     * Assert that the [with] method sets the included relationships that are sent to the
     * [TransformFactory].
     */
    public function testWithMethodSetsIncludedRelationsSentToFactory(): void
    {
        $this->transformFactory->shouldReceive('make')->andReturn([]);

        $this->builder->resource()->with($relations = ['foo', 'bar'])->transform();

        $this->transformFactory->shouldHaveReceived('make')->with($this->resource, $this->serializer, [
            'includes' => $relations,
            'excludes' => [],
            'fieldsets' => [],
        ])->once();
    }

    /**
     * Assert that the [with] method allows to be called multiple times and accepts strings
     * as parameters.
     */
    public function testWithMethodAllowsMultipleCallsAndStrings(): void
    {
        $this->transformFactory->shouldReceive('make')->andReturn([]);

        $this->builder->resource()->with('foo')->with('bar', 'baz')->transform();

        $this->transformFactory->shouldHaveReceived('make')->with($this->resource, $this->serializer, [
            'includes' => ['foo', 'bar', 'baz'],
            'excludes' => [],
            'fieldsets' => [],
        ])->once();
    }

    /**
     * Assert that the [without] method sets the excluded relationships that are sent to the
     * [TransformFactory].
     */
    public function testWithoutMethodSetsExcludedRelationsSentToFactory(): void
    {
        $this->transformFactory->shouldReceive('make')->andReturn([]);

        $this->builder->resource()->without($relations = ['foo', 'bar'])->transform();

        $this->transformFactory->shouldHaveReceived('make')->with($this->resource, $this->serializer, [
            'includes' => [],
            'excludes' => $relations,
            'fieldsets' => [],
        ])->once();
    }

    /**
     * Assert that the [with] method allows to be called multiple times and accepts strings
     * as parameters.
     */
    public function testWithoutMethodAllowsMultipleCallsAndStrings(): void
    {
        $this->transformFactory->shouldReceive('make')->andReturn([]);

        $this->builder->resource()->without('foo')->without('bar', 'baz')->transform();

        $this->transformFactory->shouldHaveReceived('make')->with($this->resource, $this->serializer, [
            'includes' => [],
            'excludes' => ['foo', 'bar', 'baz'],
            'fieldsets' => [],
        ])->once();
    }

    /**
     * Assert that the [transform] method extracts default relationships from transformer and
     * automatically eager loads all relationships.
     */
    public function testTransformMethodExtractsAndEagerLoadsRelations(): void
    {
        $this->transformFactory->shouldReceive('make')->andReturn([]);
        $this->resource->shouldReceive('getData')->andReturn($model = Mockery::mock(Model::class));
        $model->shouldReceive('load')->andReturnSelf();
        $this->resource->shouldReceive('getTransformer')->andReturn($transformer = Mockery::mock(Transformer::class));
        $transformer->shouldReceive('relations')->andReturn(['foo' => null, 'bar' => null]);
        $transformer->shouldReceive('defaultRelations')->andReturn(['baz' => null]);

        $this->builder->resource()->with($relations = ['foo' => function () { }, 'bar'])->transform();

        $model->shouldHaveReceived('load')->with(Mockery::on(function ($argument) {
            return Arr::has($argument, ['foo', 'bar', 'baz']);
        }))->once();
        $this->transformFactory->shouldHaveReceived('make')->with($this->resource, $this->serializer, [
            'includes' => ['foo', 'bar', 'baz'],
            'excludes' => [],
            'fieldsets' => [],
        ])->once();
    }

    /**
     * Assert that the [transform] method extracts default relationships from transformer and
     * automatically eager loads all relationships even when the relation name contains
     * inclusion parameters separated with a colon.
     */
    public function testTransformMethodExtractsAndEagerLoadsRelationsWithParameters(): void
    {
        $this->transformFactory->shouldReceive('make')->andReturn([]);
        $this->resource->shouldReceive('getData')->andReturn($model = Mockery::mock(Model::class));
        $model->shouldReceive('load')->andReturnSelf();
        $this->resource->shouldReceive('getTransformer')->andReturn($transformer = Mockery::mock(Transformer::class));
        $transformer->shouldReceive('relations')->andReturn(['foo:first(aa|bb)' => null, 'bar:second(cc|dd)' => null]);
        $transformer->shouldReceive('defaultRelations')->andReturn([]);

        $this->builder->resource()->with(['foo:first(aa|bb)', 'bar:second(cc|dd)'])->transform();

        $model->shouldHaveReceived('load')->with(Mockery::on(function ($argument) {
            return Arr::has($argument, ['foo', 'bar']);
        }))->once();
        $this->transformFactory->shouldHaveReceived('make')->with($this->resource, $this->serializer, [
            'includes' => ['foo:first(aa|bb)', 'bar:second(cc|dd)'],
            'excludes' => [],
            'fieldsets' => [],
        ])->once();
    }

    /**
     * Assert that the [transform] method doesn't eager load relations not present in the $relations list.
     */
    public function testTransformMethodDoesntEagerLoadNonListedRelations(): void
    {
        $this->transformFactory->shouldReceive('make')->andReturn([]);
        $this->resource->shouldReceive('getData')->andReturn($model = Mockery::mock(Model::class));
        $model->shouldReceive('load')->andReturnSelf();
        $this->resource->shouldReceive('getTransformer')->andReturn($transformer = Mockery::mock(Transformer::class));
        $transformer->shouldReceive('relations')->andReturn(['foo' => null]);
        $transformer->shouldReceive('defaultRelations')->andReturn([]);

        $this->builder->resource()->with(['foo', 'bar'])->transform();

        $model->shouldHaveReceived('load')->with(Mockery::on(function ($argument) {
            return Arr::has($argument, 'foo') && count($argument) === 1;
        }))->once();
        $this->transformFactory->shouldHaveReceived('make')->with($this->resource, $this->serializer, [
            'includes' => ['foo'],
            'excludes' => [],
            'fieldsets' => [],
        ])->once();
    }

    /**
     * Assert that the [transform] method doesn't eager load relations which has an include method.
     */
    public function testTransformMethodDoesntEagerLoadRelationsWithAnIncludeMethod(): void
    {
        $this->transformFactory->shouldReceive('make')->andReturn([]);
        $this->resource->shouldReceive('getData')->andReturn($model = Mockery::mock(Model::class));
        $model->shouldReceive('load')->andReturnSelf();
        $transformer = Mockery::mock(TransformerWithIncludeMethods::class);
        $this->resource->shouldReceive('getTransformer')->andReturn($transformer);
        $transformer->shouldReceive([
            'relations' => ['foo' => null, 'bar' => null],
            'defaultRelations' => ['baz' => null],
        ]);

        $this->builder->resource()->with($relations = ['foo', 'bar'])->transform();

        $model->shouldHaveReceived('load')->with(Mockery::on(function ($argument) {
            return Arr::has($argument, 'foo') && count($argument) === 1;
        }))->once();
        $this->transformFactory->shouldHaveReceived('make')->with($this->resource, $this->serializer, [
            'includes' => ['foo', 'bar', 'baz'],
            'excludes' => [],
            'fieldsets' => [],
        ])->once();
    }

    /**
     * Assert that the [only] method sets the filtered fields that are sent to the
     * [TransformFactory].
     */
    public function testOnlyMethodSetsFilteredFieldsSentToFactory(): void
    {
        $this->transformFactory->shouldReceive('make')->andReturn([]);

        $this->builder->resource()->only($fields = ['foo', 'bar'])->transform();

        $this->transformFactory->shouldHaveReceived('make')->with($this->resource, $this->serializer, [
            'includes' => [],
            'excludes' => [],
            'fieldsets' => $fields,
        ])->once();
    }

    /**
     * Assert that the [only] method allows to be called multiple times and accepts strings
     * as parameters.
     */
    public function testOnlyMethodAllowsMultipleCallsAndStrings(): void
    {
        $this->transformFactory->shouldReceive('make')->andReturn([]);

        $this->builder->resource()->only('foo')->only('bar', 'baz')->transform();

        $this->transformFactory->shouldHaveReceived('make')->with($this->resource, $this->serializer, [
            'includes' => [],
            'excludes' => [],
            'fieldsets' => ['foo', 'bar', 'baz'],
        ])->once();
    }
}

class TransformerWithIncludeMethods extends Transformer
{
    public function includeBar()
    {
    }

    public function includeBaz()
    {
    }
}
