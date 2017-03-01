<?php

namespace Flugg\Responder\Tests;

use Flugg\Responder\Contracts\Transformable;
use Flugg\Responder\Http\ErrorResponseBuilder;
use Flugg\Responder\Http\SuccessResponseBuilder;
use Flugg\Responder\ResourceFactory;
use Flugg\Responder\ResponderServiceProvider;
use Flugg\Responder\Transformer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\ResourceInterface;
use Mockery;
use Orchestra\Testbench\TestCase as BaseTestCase;

/**
 * This is the base test case class and is where the testing environment bootstrapping
 * takes place. All other testing classes should extend from this class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:'
        ]);
    }

    /**
     * Get package service providers.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            ResponderServiceProvider::class
        ];
    }

    /**
     * Makes a new empty model for testing purposes.
     *
     * @param array $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function makeModel(array $attributes = []):Model
    {
        $model = new class extends Model
        {
            protected $guarded = [];
        };

        return $model->newInstance($attributes);
    }

    /**
     * Makes a new empty transformable model with a transformer set.
     *
     * @param  \Flugg\Responder\Transformer|string $transformer
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function makeModelWithTransformer($transformer):Model
    {
        $this->app->bind('tests.model_transformer', function () use ($transformer) {
            return $transformer;
        });

        return new class extends Model implements Transformable
        {
            public static function transformer()
            {
                return app('tests.model_transformer');
            }
        };
    }

    /**
     * Makes a new empty model with a resource key set.
     *
     * @param  string $resourceKey
     * @return Model
     */
    protected function makeModelWithResourceKey(string $resourceKey):Model
    {
        $this->app->bind('tests.resource_key', function () use ($resourceKey) {
            return $resourceKey;
        });

        return new class extends Model
        {
            public static function getResourceKey()
            {
                return app('tests.resource_key');
            }
        };
    }

    /**
     * Makes a new transformer for testing purposes.
     *
     * @return Transformer
     */
    protected function makeTransformer():Transformer
    {
        return new class extends Transformer
        {
            public function transform($model):array
            {
                return $model->toArray;
            }
        };
    }

    /**
     * Create a mock of the Eloquent builder with a mock of the [get] method which returns
     * the given data.
     *
     * @param array $data
     * @return \Mockery\MockInterface
     */
    protected function mockBuilder(array $data = null)
    {
        $builder = Mockery::spy(Builder::class);
        $builder->shouldReceive('get')->andReturn(collect($data));

        return $builder;
    }

    /**
     * Create a mock of the Eloquent builder with a mock of the [paginate] method which
     * returns an instance of [\Illuminate\Pagination\LengthAwarePaginator].
     *
     * @param  array $data
     * @return \Mockery\MockInterface
     */
    protected function mockBuilderWithPaginator(array $data = null)
    {
        $paginator = new LengthAwarePaginator($data, count($data), 15);
        $builder = $this->mockBuilder($data);
        $builder->shouldReceive('paginate')->andReturn($paginator);

        return $builder;
    }

    /**
     * Create a mock of an Eloquent relationship.
     *
     * @param  Collection|Model|null $data
     * @return \Mockery\MockInterface
     */
    protected function mockRelation($data = null)
    {
        $relation = Mockery::spy(Relation::class);
        $relation->shouldReceive('get')->andReturn(collect($data));

        return $relation;
    }

    /**
     * Create a mock of a pivot.
     *
     * @return \Mockery\MockInterface
     */
    protected function mockPivot()
    {
        return Mockery::spy(Pivot::class);
    }

    /**
     * Create a mock of a resource factory.
     *
     * @param  \League\Fractal\Resource\ResourceInterface $resource
     * @return \Mockery\MockInterface
     */
    protected function mockResourceFactory(ResourceInterface $resource)
    {
        $factory = Mockery::spy(ResourceFactory::class);
        $factory->shouldReceive('make')->andReturn($resource);

        $this->app->instance(ResourceFactory::class, $factory);

        return $factory;
    }

    /**
     * Create a mock of a success response builder.
     *
     * @return \Mockery\MockInterface
     */
    protected function mockSuccessResponseBuilder()
    {
        $builder = Mockery::spy(SuccessResponseBuilder::class);
        $builder->shouldReceive('transform')->andReturnSelf();
        $builder->shouldReceive('addMeta')->andReturnSelf();
        $builder->shouldReceive('respond')->andReturn(new JsonResponse);

        $this->app->instance(SuccessResponseBuilder::class, $builder);

        return $builder;
    }

    /**
     * Create a mock of a success response builder.
     *
     * @return \Mockery\MockInterface
     */
    protected function mockErrorResponseBuilder()
    {
        $builder = Mockery::spy(ErrorResponseBuilder::class);
        $builder->shouldReceive('setError')->andReturnSelf();
        $builder->shouldReceive('respond')->andReturn(new JsonResponse);

        $this->app->instance(ErrorResponseBuilder::class, $builder);

        return $builder;
    }
}