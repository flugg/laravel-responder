<?php

namespace Flugg\Responder\Tests\Unit;

use Flugg\Responder\Exceptions\InvalidSerializerException;
use Flugg\Responder\FractalTransformFactory;
use Flugg\Responder\Serializers\NullSerializer;
use Flugg\Responder\Tests\TestCase;
use Flugg\Responder\TransformBuilder;
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
     * Mock of the resource builder.
     *
     * @var \Mockery\MockInterface
     */
    protected $resourceBuilder;

    /**
     * Mock of the transform factory.
     *
     * @var \Mockery\MockInterface
     */
    protected $factory;

    /**
     * The transform builder.
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

        $this->resourceBuilder = $this->mockResourceBuilder();
        $this->factory = Mockery::mock(FractalTransformFactory::class);
        $this->builder = (new TransformBuilder($this->resourceBuilder, $this->factory));
    }

    /**
     *
     */
    public function testResourceMethodMakesAResource()
    {
        [$data, $transformer, $resourceKey] = [['foo' => 1], $this->mockTransformer(), 'foo'];

        $result = $this->builder->resource($data, $transformer, $resourceKey);

        $this->assertSame($this->builder, $result);
        $this->resourceBuilder->shouldHaveReceived('make')->with($data, $transformer)->once();
        $this->resourceBuilder->shouldHaveReceived('withResourceKey')->with($resourceKey)->once();
    }

    /**
     *
     */
    public function testAddMetaMethodAddsMetaToTheResourceBuilder()
    {
        $result = $this->builder->addMeta($meta = ['foo' => 1]);

        $this->assertSame($this->builder, $result);
        $this->resourceBuilder->shouldHaveReceived('withMeta')->with($meta)->once();
    }

    /**
     *
     */
    public function testTransformMethodExecutesTheTransform()
    {
        $this->builder->serializer($serializer = new NullSerializer);
        $this->resourceBuilder->shouldReceive('get')->andReturn($resource = new NullResource);
        $this->factory->shouldReceive('make')->andReturn($data = ['foo' => 1]);

        $result = $this->builder->transform();

        $this->assertEquals($data, $result);
        $this->factory->shouldHaveReceived('make')->with($resource, $serializer, null, null)->once();
    }

    /**
     *
     */
    public function testSerializerMethodSetsTheSerializerSentToFactory()
    {
        $this->resourceBuilder->shouldReceive('get')->andReturn($resource = new NullResource);
        $this->factory->shouldReceive('make')->andReturn([]);

        $this->builder->serializer($serializer = new JsonApiSerializer)->transform();

        $this->factory->shouldHaveReceived('make')->with($resource, $serializer, null, null)->once();
    }

    /**
     *
     */
    public function testSerializerMethodAcceptsClassNameString()
    {
        $this->resourceBuilder->shouldReceive('get')->andReturn($resource = new NullResource);
        $this->factory->shouldReceive('make')->andReturn([]);

        $this->builder->serializer($serializer = JsonApiSerializer::class)->transform();

        $this->factory->shouldHaveReceived('make')->with($resource, $serializer, null, null)->once();
    }

    /**
     *
     */
    public function testSerializerMethodThrowsExceptionWhenGivenInvalidSerializer()
    {
        $this->expectException(InvalidSerializerException::class);

        $this->builder->serializer($serializer = stdClass::class)->transform();

        $this->factory->shouldHaveReceived('make')->with(null, $serializer, null, null)->once();
    }

    /**
     *
     */
    public function testWithMethodSetsIncludedRelationsSentToFactory()
    {
        $this->builder->serializer($serializer = new NullSerializer);
        $this->resourceBuilder->shouldReceive('get')->andReturn($resource = new NullResource);
        $this->factory->shouldReceive('make')->andReturn([]);

        $this->builder->with($relations = ['foo', 'bar'])->transform();

        $this->factory->shouldHaveReceived('make')->with($resource, $serializer, $relations, null)->once();
    }

    /**
     *
     */
    public function testWithMethodCanBeCalledMultipleTimesAndAllowsString()
    {
        $this->builder->serializer($serializer = new NullSerializer);
        $this->resourceBuilder->shouldReceive('get')->andReturn($resource = new NullResource);
        $this->factory->shouldReceive('make')->andReturn([]);

        $this->builder->with('foo')->with('bar', 'baz')->transform();

        $this->factory->shouldHaveReceived('make')->with($resource, $serializer, ['foo', 'bar', 'baz'], null)->once();
    }

    /**
     *
     */
    public function testWithoutMethodSetsExcludedRelationsSentToFactory()
    {
        $this->builder->serializer($serializer = new NullSerializer);
        $this->resourceBuilder->shouldReceive('get')->andReturn($resource = new NullResource);
        $this->factory->shouldReceive('make')->andReturn([]);

        $this->builder->without($relations = ['foo', 'bar'])->transform();

        $this->factory->shouldHaveReceived('make')->with($resource, $serializer, null, $relations)->once();
    }

    /**
     *
     */
    public function testWithoutMethodCanBeCalledMultipleTimesAndAllowsStrings()
    {
        $this->builder->serializer($serializer = new NullSerializer);
        $this->resourceBuilder->shouldReceive('get')->andReturn($resource = new NullResource);
        $this->factory->shouldReceive('make')->andReturn([]);

        $this->builder->without('foo')->without('bar', 'baz')->transform();

        $this->factory->shouldHaveReceived('make')->with($resource, $serializer, null, ['foo', 'bar', 'baz'])->once();
    }
}