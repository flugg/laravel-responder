<?php

namespace Flugg\Responder\Tests;

use Flugg\Responder\Contracts\Transformable;
use Flugg\Responder\Http\SuccessResponseBuilder;
use Flugg\Responder\ResourceFactory;
use Flugg\Responder\Responder;
use Flugg\Responder\ResponderServiceProvider;
use Flugg\Responder\Traits\RespondsWithJson;
use Flugg\Responder\Transformer;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Translation\Translator;
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
     * Save an instance of the schema builder for easy access.
     *
     * @var Builder
     */
    protected $schema;

    /**
     * An instance of the responder service responsible for generating API responses.
     *
     * @var Responder
     */
    protected $responder;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->app['config']->set('responder.include_success_flag', true);
        $this->app['config']->set('responder.include_status_code', false);
        $this->responder = $this->app[Responder::class];

        $this->createTestTransformer();

        $this->schema = $this->app['db']->connection()->getSchemaBuilder();
        $this->runTestMigrations();

        $this->beforeApplicationDestroyed(function () {
            $this->rollbackTestMigrations();
        });
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
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            ResponderServiceProvider::class
        ];
    }

    /**
     * Run migrations for tables only used for testing purposes.
     *
     * @return void
     */
    protected function runTestMigrations()
    {
        if (! $this->schema->hasTable('fruits')) {
            $this->schema->create('fruits', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->integer('price');
                $table->boolean('is_rotten');
                $table->timestamps();
            });
        }
    }

    /**
     * Rollback migrations for tables only used for testing purposes.
     *
     * @return void
     */
    protected function rollbackTestMigrations()
    {
        $this->schema->drop('fruits');
    }

    /**
     * Creates a controller class with the RespondsWithJson trait.
     *
     * @return Controller
     */
    protected function createTestController()
    {
        return new class extends Controller
        {
            use RespondsWithJson;

            public function successAction($fruit)
            {
                return $this->successResponse($fruit);
            }

            public function errorAction()
            {
                return $this->errorResponse('test_error', 400, 'Test error.');
            }
        };
    }

    /**
     * Creates a new transformer for testing purposes.
     *
     * @return void
     */
    protected function createTestTransformer()
    {
        $transformer = new class extends Transformer
        {
            public function transform($model):array
            {
                return [
                    'name' => (string) $model->name,
                    'price' => (int) $model->price,
                    'isRotten' => (bool) false
                ];
            }
        };

        $this->app->bind('test.transformer', function () use ($transformer) {
            return new $transformer();
        });
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
     * Makes a new empty model for testing purposes.
     *
     * @return Model
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
     * Makes a new empty transformable model with a transformer set.
     *
     * @return Model
     */
    protected function makeModelWithTransformer($transformer):Model
    {
        $this->app->bind('tests.model_transformer', function () use ($transformer) {
            return new $transformer;
        });

        return new class extends Model implements Transformable
        {
            protected $table = 'foo';

            public static function transformer()
            {
                return app('tests.model_transformer');
            }
        };
    }

    /**
     * Creates a new adjustable model for testing purposes.
     *
     * @param  array $attributes
     * @return Model
     */
    protected function createModel(array $attributes = []):Model
    {
        $model = new class extends Model implements Transformable
        {
            protected $fillable = ['name', 'price', 'is_rotten'];
            protected $table = 'fruits';

            public static function transformer():string
            {
                return get_class(app('test.transformer'));
            }
        };

        return $this->storeModel($model, $attributes);
    }

    /**
     * Creates a new adjustable model without an attached transformer for testing purposes.
     *
     * @param  array $attributes
     * @return Model
     */
    protected function createTestModelWithNoTransformer(array $attributes = []):Model
    {
        $model = new class extends Model
        {
            protected $fillable = ['name', 'price', 'is_rotten'];
            protected $table = 'fruits';
        };

        return $this->storeModel($model, $attributes);
    }

    /**
     * Creates a new adjustable model with a null transformer for testing purposes.
     *
     * @param  array $attributes
     * @return Model
     */
    protected function createTestModelWithNullTransformer(array $attributes = []):Model
    {
        $model = new class extends Model implements Transformable
        {
            protected $fillable = ['name', 'price', 'is_rotten'];
            protected $table = 'fruits';

            public static function transformer()
            {
                return null;
            }
        };

        return $this->storeModel($model, $attributes);
    }

    /**
     * Stores an actual instance of an adjustable model to the database.
     *
     * @param  Model $model
     * @param  array $attributes
     * @return Model
     */
    protected function storeModel(Model $model, array $attributes = []):Model
    {
        return $model->create(array_merge([
            'name' => 'Mango',
            'price' => 10,
            'is_rotten' => false
        ], $attributes));
    }

    /**
     * Create a mock of a resource factory.
     *
     * @param  $resource
     * @return \Mockery\MockInterface
     */
    protected function mockResourceFactory(ResourceInterface $resource)
    {
        $resourceFactory = Mockery::spy(ResourceFactory::class);
        $resourceFactory->shouldReceive('make')->andReturn($resource);

        $this->app->instance(ResourceFactory::class, $resourceFactory);

        return $resourceFactory;
    }

    /**
     * Create a mock of the Eloquent builder with a mock of the [get] method which returns
     * the given data.
     *
     * @param  array $data
     * @return \Mockery\MockInterface
     */
    protected function mockBuilder(array $data = null)
    {
        $builder = Mockery::spy(EloquentBuilder::class);
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
     * Create a mock of the responder and binds it to the service container.
     *
     * @return \Mockery\MockInterface
     */
    protected function mockResponder()
    {
        $responder = Mockery::spy(Responder::class);
        $responder->shouldReceive('success')->andReturn(new JsonResponse());

        $this->app->instance(Responder::class, $responder);

        return $responder;
    }

    /**
     * Create a mock of Laravel's translator and binds it to the service container.
     *
     * @param  string $message
     * @return \Mockery\MockInterface
     */
    protected function mockTranslator(string $message)
    {
        $translator = Mockery::spy(Translator::class);

        $translator->shouldReceive('has')->andReturn(true);
        $translator->shouldReceive('trans')->andReturn($message);

        $this->app->loadDeferredProvider('translator');
        $this->app->instance('translator', $translator);

        return $translator;
    }

    /**
     * Create a mock of a success response builder.
     *
     * @return \Mockery\MockInterface
     */
    protected function mockSuccessBuilder()
    {
        $successBuilder = Mockery::spy(SuccessResponseBuilder::class);
        $successBuilder->shouldReceive('transform')->andReturnSelf();
        $successBuilder->shouldReceive('addMeta')->andReturnSelf();
        $successBuilder->shouldReceive('respond')->andReturn(new JsonResponse);

        $this->app->instance(SuccessResponseBuilder::class, $successBuilder);

        return $successBuilder;
    }
}