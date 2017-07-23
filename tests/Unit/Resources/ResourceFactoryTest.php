<?php

namespace Flugg\Responder\Tests\Unit\Resources;

use Flugg\Responder\Pagination\CursorFactory;
use Flugg\Responder\Resources\DataNormalizer;
use Flugg\Responder\Resources\ResourceFactory;
use Flugg\Responder\Tests\TestCase;
use Flugg\Responder\Transformers\TransformerManager;
use Flugg\Responder\Transformers\TransformerResolver;
use Illuminate\Database\Eloquent\Model;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
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
     * Mock of a resource data normalizer class.
     *
     * @var \Mockery\MockInterface
     */
    protected $normalizer;

    /**
     * Mock of a transformer resolver class.
     *
     * @var \Mockery\MockInterface
     */
    protected $transformerResolver;

    /**
     * The resource factory being tested.
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

        $this->normalizer = Mockery::mock(DataNormalizer::class);
        $this->transformerResolver = Mockery::mock(TransformerResolver::class);
        $this->factory = new ResourceFactory($this->normalizer, $this->transformerResolver);
    }

    /**
     *
     */
    public function testMakeMethodMakesANullResourceWhenGivenNoArguments()
    {
        $this->normalizer->shouldReceive('normalize')->andReturn(null);

        $resource = $this->factory->make();

        $this->assertInstanceOf(NullResource::class, $resource);
        $this->normalizer->shouldHaveReceived('normalize')->with(null);
    }

    /**
     *
     */
    public function testMakeMethodMakesACollectionResourceWhenGivenArray()
    {
        $this->normalizer->shouldReceive('normalize')->andReturn($data = ['foo', 'bar']);
        $this->transformerResolver->shouldReceive('resolve')->andReturn($transformer = $this->mockTransformer());

        $resource = $this->factory->make($data, $transformer, $resourceKey = 'bar');

        $this->assertInstanceOf(Collection::class, $resource);
        $this->assertEquals($data, $resource->getData());
        $this->assertSame($transformer, $resource->getTransformer());
        $this->assertEquals($resourceKey, $resource->getResourceKey());
        $this->normalizer->shouldHaveReceived('normalize')->with($data)->once();
        $this->transformerResolver->shouldHaveReceived('resolve')->with($transformer)->once();
    }

    /**
     *
     */
    public function testMakeMethodMakesAnItemResourceWhenGivenArray()
    {
        $this->normalizer->shouldReceive('normalize')->andReturn($data = Mockery::mock(Model::class));
        $this->transformerResolver->shouldReceive('resolve')->andReturn($transformer = $this->mockTransformer());

        $resource = $this->factory->make($data, $transformer, $resourceKey = 'bar');

        $this->assertInstanceOf(Item::class, $resource);
        $this->assertEquals($data, $resource->getData());
        $this->assertSame($transformer, $resource->getTransformer());
        $this->assertEquals($resourceKey, $resource->getResourceKey());
        $this->normalizer->shouldHaveReceived('normalize')->with($data)->once();
        $this->transformerResolver->shouldHaveReceived('resolve')->with($transformer)->once();
    }

    /**
     *
     */
    public function testMakeMethodResolvesTransformerFromDataWhenGivenNoTransformer()
    {
        $this->normalizer->shouldReceive('normalize')->andReturn($data = Mockery::mock(Model::class));
        $this->transformerResolver->shouldReceive('resolveFromData')->andReturn($transformer = $this->mockTransformer());

        $this->factory->make($data);

        $this->transformerResolver->shouldHaveReceived('resolveFromData')->with($data)->once();
    }
}