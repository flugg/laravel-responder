<?php

namespace Flugg\Responder\Tests\Unit\Transformers;

use Flugg\Responder\Contracts\Transformable;
use Flugg\Responder\Exceptions\InvalidTransformerException;
use Flugg\Responder\Tests\TestCase;
use Flugg\Responder\Transformers\TransformerResolver;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Mockery;
use stdClass;

/**
 * Unit tests for the abstract [Flugg\Responder\Transformers\TransformerResolver] class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
final class TransformerResolverTest extends TestCase
{
    /**
     * A mock of a [Container] class.
     *
     * @var \Mockery\MockInterface
     */
    protected $container;

    /**
     * A mock of a [Transformer] class.
     *
     * @var \Mockery\MockInterface
     */
    protected $fallbackTransformer;

    /**
     * The [TransformerResolver] class being tested.
     *
     * @var \Flugg\Responder\Transformers\TransformerResolver
     */
    protected $resolver;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->container = Mockery::mock(Container::class);
        $this->fallbackTransformer = $this->mockTransformer();
        $this->resolver = new TransformerResolver($this->container, get_class($this->fallbackTransformer));
    }

    /**
     * Assert that the [resolve] method resolves transformer from IoC container when given a string.
     */
    public function testResolveMethodShouldResolveTransformerFromContainerIfGivenString(): void
    {
        $this->container->shouldReceive('make')->andReturn($transformer = $this->mockTransformer());

        $result = $this->resolver->resolve($class = 'foo');

        $this->assertSame($transformer, $result);
        $this->container->shouldHaveReceived('make')->with($class)->once();
    }

    /**
     * Assert that the [resolve] method returns the transformer given if it's a valid transformer.
     */
    public function testResolveMethodShouldReturnTransformerIfValid(): void
    {
        $result = $this->resolver->resolve($transformer = $this->mockTransformer());

        $this->assertSame($transformer, $result);
    }

    /**
     * Assert that the [resolve] method also returns the transformer given if it's a closure.
     */
    public function testResolveMethodShouldReturnTransformerIfClosure(): void
    {
        $result = $this->resolver->resolve($transformer = function () { });

        $this->assertSame($transformer, $result);
    }

    /**
     * Assert that the [resolve] method throws an [InvalidTransformerException] on invalid transformers.
     */
    public function testResolveMethodShouldThrowExceptionIfGivenInvalidTransformer(): void
    {
        $this->expectException(InvalidTransformerException::class);
        $this->expectExceptionMessage('Transformer must be a callable or an instance of [Flugg\Responder\Transformers\Transformer].');

        $this->resolver->resolve(new stdClass());
    }

    /**
     * Assert that the [resolveFromData] method resolves transformer from cache if binding is set.
     */
    public function testResolveFromDataMethodShouldResolveTransformerFromBinding(): void
    {
        $model = Mockery::mock(Model::class);
        $this->resolver->bind(get_class($model), $transformer = $this->mockTransformer());

        $result = $this->resolver->resolveFromData($model);

        $this->assertSame($transformer, $result);
    }

    /**
     * Assert that the [resolveFromData] method resolves transformer from an element implementing the
     * [Transformable] contract if one is given.
     */
    public function testResolveFromDataMethodShouldResolveTransformerFromTransformable(): void
    {
        $model = Mockery::mock(Transformable::class);
        $model->shouldReceive('transformer')->andReturn($transformer = $this->mockTransformer());

        $result = $this->resolver->resolveFromData($model);

        $this->assertSame($transformer, $result);
        $model->shouldHaveReceived('transformer')->once();
    }

    /**
     * Assert that the [resolveFromData] method resolves an element implementing the [Transformable] contract
     * to resolve a transformer from, from a list of items.
     */
    public function testResolveFromDataMethodShouldResolveTransformableFromCollection(): void
    {
        $model = Mockery::mock(Transformable::class);
        $model->shouldReceive('transformer')->andReturn($transformer = $this->mockTransformer());

        $result = $this->resolver->resolveFromData([$model]);

        $this->assertSame($transformer, $result);
        $model->shouldHaveReceived('transformer')->once();
    }

    /**
     * Assert that the [resolveFromData] method resolves an automatic closure transformer if no other
     * can be resolved.
     */
    public function testResolveFromDataMethodShouldResolveAFallbackTransformer(): void
    {
        $model = Mockery::mock(Model::class);
        $this->container->shouldReceive('make')->andReturn($this->fallbackTransformer);

        $result = $this->resolver->resolveFromData($model);

        $this->assertSame($this->fallbackTransformer, $result);
    }
}