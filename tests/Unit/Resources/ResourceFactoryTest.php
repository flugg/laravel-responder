<?php

namespace Flugg\Responder\Tests\Unit\Resources;

use Flugg\Responder\Pagination\CursorFactory;
use Flugg\Responder\Resources\DataNormalizer;
use Flugg\Responder\Resources\ResourceFactory;
use Flugg\Responder\Resources\ResourceKeyResolver;
use Flugg\Responder\Tests\TestCase;
use Flugg\Responder\Transformers\TransformerManager;
use Flugg\Responder\Transformers\TransformerResolver;
use Illuminate\Database\Eloquent\Model;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\NullResource;
use League\Fractal\Resource\Primitive;
use Mockery;

/**
 * Unit tests for the [Flugg\Responder\Resources\ResourceFactory] class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
final class ResourceFactoryTest extends TestCase
{
    /**
     * A mock of a [DataNormalizer] class.
     *
     * @var \Mockery\MockInterface
     */
    protected $normalizer;

    /**
     * A mock of a [TransformerResolver] class.
     *
     * @var \Mockery\MockInterface
     */
    protected $transformerResolver;

    /**
     * A mock of a [ResourceKeyResolver] class.
     *
     * @var \Mockery\MockInterface
     */
    protected $resourceKeyResolver;

    /**
     * The [ResourceFactory] class being tested.
     *
     * @var \Flugg\Responder\Resources\ResourceFactory
     */
    protected $factory;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->normalizer = Mockery::mock(DataNormalizer::class);
        $this->transformerResolver = Mockery::mock(TransformerResolver::class);
        $this->resourceKeyResolver = Mockery::mock(ResourceKeyResolver::class);
        $this->factory = new ResourceFactory($this->normalizer, $this->transformerResolver, $this->resourceKeyResolver);
    }

    /**
     * Assert that the [make] method makes a [NullResource] resource when given no arguments.
     */
    public function testMakeMethodShouldMakeNullResourcesWhenGivenNoArguments(): void
    {
        $this->normalizer->shouldReceive('normalize')->andReturn(null);

        $resource = $this->factory->make();

        $this->assertInstanceOf(NullResource::class, $resource);
        $this->normalizer->shouldHaveReceived('normalize')->with(null)->once();
    }

    /**
     * Assert that the [make] method makes an [Item] resource when given a model.
     */
    public function testMakeMethodShouldMakeItemResourcesWhenGivenModels(): void
    {
        $this->transformerResolver->shouldReceive('resolve')->andReturn($transformer = $this->mockTransformer());
        $this->normalizer->shouldReceive('normalize')->andReturn($data = Mockery::mock(Model::class));

        $resource = $this->factory->make($data, $transformer, $resourceKey = 'bar');

        $this->assertInstanceOf(Item::class, $resource);
        $this->assertEquals($data, $resource->getData());
        $this->assertSame($transformer, $resource->getTransformer());
        $this->assertEquals($resourceKey, $resource->getResourceKey());
        $this->normalizer->shouldHaveReceived('normalize')->with($data)->once();
        $this->transformerResolver->shouldHaveReceived('resolve')->with($transformer)->once();
    }

    /**
     * Assert that the [make] method makes a [Collection] resource when given arrays
     * containing arrays or objects.
     */
    public function testMakeMethodShouldMakeCollectionResourcesWhenGivenArraysWithNonScalars(): void
    {
        $this->transformerResolver->shouldReceive('resolve')->andReturn($transformer = $this->mockTransformer());
        $this->normalizer->shouldReceive('normalize')->andReturn($data = [
            ['foo' => 1],
            ['bar' => 2],
        ]);

        $resource = $this->factory->make($data, $transformer, $resourceKey = 'bar');

        $this->assertInstanceOf(Collection::class, $resource);
        $this->assertEquals($data, $resource->getData());
        $this->assertSame($transformer, $resource->getTransformer());
        $this->assertEquals($resourceKey, $resource->getResourceKey());
        $this->normalizer->shouldHaveReceived('normalize')->with($data)->once();
        $this->transformerResolver->shouldHaveReceived('resolve')->with($transformer)->once();
    }

    /**
     * Assert that the [make] method makes a [Primitive] resource when given a scalar.
     */
    public function testMakeMethodShouldMakePrimitiveResourcesWhenGivenAScalar(): void
    {
        $this->transformerResolver->shouldReceive('resolve')->andReturn($transformer = $this->mockTransformer());
        $this->normalizer->shouldReceive('normalize')->andReturn($data = 'foo');

        $resource = $this->factory->make($data, $transformer, $resourceKey = 'bar');

        $this->assertInstanceOf(Primitive::class, $resource);
        $this->assertEquals($data, $resource->getData());
        $this->assertSame($transformer, $resource->getTransformer());
        $this->assertEquals($resourceKey, $resource->getResourceKey());
        $this->normalizer->shouldHaveReceived('normalize')->with($data)->once();
        $this->transformerResolver->shouldHaveReceived('resolve')->with($transformer)->once();
    }

    /**
     * Assert that the [make] method makes a [Item] resource when given an array with scalars.
     */
    public function testMakeMethodShouldMakeItemResourcesWhenGivenArraysWithScalars(): void
    {
        $this->transformerResolver->shouldReceive('resolve')->andReturn($transformer = $this->mockTransformer());
        $this->normalizer->shouldReceive('normalize')->andReturn($data = ['foo' => 1, 'bar' => 2]);

        $resource = $this->factory->make($data, $transformer, $resourceKey = 'bar');

        $this->assertInstanceOf(Item::class, $resource);
    }

    /**
     * Assert that the [make] method resolves a transformer using the [TransformerResolver]
     * if no transformer is given.
     */
    public function testMakeMethodResolvesTransformerWhenNoneIsGiven(): void
    {
        $this->transformerResolver->shouldReceive('resolveFromData')
            ->andReturn($transformer = $this->mockTransformer());
        $this->resourceKeyResolver->shouldReceive('resolve')->andReturn($resourceKey = 'foo');
        $this->normalizer->shouldReceive('normalize')->andReturn($data = Mockery::mock(Model::class));

        $this->factory->make($data);

        $this->transformerResolver->shouldHaveReceived('resolveFromData')->with($data)->once();
        $this->resourceKeyResolver->shouldHaveReceived('resolve')->with($data)->once();
    }

    /**
     * Assert that the [make] method allows instances of [ResourceInterface] as data.
     */
    public function testMakeMethodShouldAllowResources(): void
    {
        $this->transformerResolver->shouldReceive('resolveFromData')
            ->andReturn($transformer = $this->mockTransformer());
        $this->resourceKeyResolver->shouldReceive('resolve')->andReturn($resourceKey = 'foo');

        $resource = $this->factory->make(new Item($data = Mockery::mock(Model::class)));

        $this->assertInstanceOf(Item::class, $resource);
        $this->assertSame($transformer, $resource->getTransformer());
        $this->transformerResolver->shouldHaveReceived('resolveFromData')->with($data)->once();
        $this->resourceKeyResolver->shouldHaveReceived('resolve')->with($data)->once();
    }
}