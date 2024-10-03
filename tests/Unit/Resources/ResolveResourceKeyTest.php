<?php

namespace Flugg\Responder\Tests\Unit\Resources;

use Flugg\Responder\Resources\ResourceKeyResolver;
use Flugg\Responder\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use Mockery;

/**
 * Unit tests for the [Flugg\Responder\Resources\ResolveResourceKeyTest] class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
final class ResolveResourceKeyTest extends TestCase
{
    /**
     * The [ResourceKeyResolver] class being tested.
     *
     * @var \Flugg\Responder\Resources\ResourceKeyResolver
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

        $this->resolver = new ResourceKeyResolver;
    }

    /**
     * Assert that the [resolve] method resolves 'data' as resource key if it can't resolve
     * a resource key from the given data.
     */
    public function testResolveMethodShouldResolveDefaultResourceKeyIfNoneIsFound(): void
    {
        $result = $this->resolver->resolve($data = []);

        $this->assertEquals('data', $result);
    }

    /**
     * Assert that the [resolve] method resolves resource key from cache if binding is set.
     */
    public function testResolveMethodShouldResolveResourceKeyFromBinding(): void
    {
        $model = Mockery::mock(Model::class);
        $this->resolver->bind(get_class($model), $resourceKey = 'foo');

        $result = $this->resolver->resolve($model);

        $this->assertEquals($resourceKey, $result);
    }

    /**
     * Assert that the [resolve] method resolves transformable from a list of transformables.
     */
    public function testResolveMethodShouldResolveTransformableFromArray(): void
    {
        $data = [$model = Mockery::mock(Model::class)];
        $this->resolver->bind(get_class($model), $resourceKey = 'foo');

        $result = $this->resolver->resolve($data);

        $this->assertEquals($resourceKey, $result);
    }

    /**
     * Assert that the [resolve] method resolves resource key from the model's table name.
     */
    public function testResolveMethodShouldResolveResourceKeyFromModelsTable(): void
    {
        $model = Mockery::mock(Model::class);
        $model->shouldReceive('getTable')->andReturn($resourceKey = 'foo');

        $result = $this->resolver->resolve($model);

        $this->assertEquals($resourceKey, $result);
    }

    /**
     * Assert that the [resolve] method resolves resource key from the [getResourceKey] method.
     */
    public function testResolveMethodShouldResolveResourceKeyFromGetResourceKeyIfMethodExists(): void
    {
        $result = $this->resolver->resolve($model = new ModelWithResourceKey);

        $this->assertEquals('foo', $result);
    }
}

class ModelWithResourceKey extends Model
{
    public function getResourceKey()
    {
        return 'foo';
    }
}