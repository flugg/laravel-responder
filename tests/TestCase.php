<?php

namespace Flugg\Responder\Tests;

use Flugg\Responder\Contracts\Responder;
use Flugg\Responder\Contracts\Transformable;
use Flugg\Responder\ResponderServiceProvider;
use Flugg\Responder\Traits\RespondsWithJson;
use Flugg\Responder\Transformer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use Illuminate\Routing\Controller;
use Illuminate\Translation\Translator;
use Mockery;
use Orchestra\Testbench\TestCase as BaseTestCase;

/**
 * This is the base test case class and is where the testing environment bootstrapping
 * takes place. All other testing classes should extend from this class.
 *
 * @package Laravel Responder
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

        $this->responder = $this->app[ Responder::class ];

        $this->createTestTransformer();

        $this->schema = $this->app[ 'db' ]->connection()->getSchemaBuilder();
        $this->runTestMigrations();

        $this->beforeApplicationDestroyed( function () {
            $this->rollbackTestMigrations();
        } );
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function getEnvironmentSetUp( $app )
    {
        $app[ 'config' ]->set( 'database.default', 'testbench' );
        $app[ 'config' ]->set( 'database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:'
        ] );
    }

    /**
     * Get package service providers.
     *
     * @return array
     */
    protected function getPackageProviders( $app )
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
        if ( ! $this->schema->hasTable( 'fruits' ) ) {
            $this->schema->create( 'fruits', function ( Blueprint $table ) {
                $table->increments( 'id' );
                $table->string( 'name' );
                $table->integer( 'price' );
                $table->boolean( 'is_rotten' );
                $table->timestamps();
            } );
        }
    }

    /**
     * Rollback migrations for tables only used for testing purposes.
     *
     * @return void
     */
    protected function rollbackTestMigrations()
    {
        $this->schema->drop( 'fruits' );
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

            public function successAction( $fruit )
            {
                return $this->successResponse( $fruit );
            }

            public function errorAction()
            {
                return $this->errorResponse( 'test_error', 400, 'Test error.' );
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
            public function transform( $model ):array
            {
                return [
                    'name' => (string) $model->name,
                    'price' => (int) $model->price,
                    'isRotten' => (bool) false
                ];
            }
        };

        $this->app->bind( 'test.transformer', function () use ( $transformer ) {
            return new $transformer();
        } );
    }

    /**
     * Creates a new adjustable model for testing purposes.
     *
     * @param  array $attributes
     * @return Model
     */
    protected function createTestModel( array $attributes = [ ] ):Model
    {
        $model = new class extends Model implements Transformable
        {
            protected $fillable = [ 'name', 'price', 'is_rotten' ];
            protected $table = 'fruits';

            public static function transformer():string
            {
                return get_class( app( 'test.transformer' ) );
            }
        };

        return $this->storeModel( $model, $attributes );
    }

    /**
     * Creates a new adjustable model without an attached transformer for testing purposes.
     *
     * @param  array $attributes
     * @return Model
     */
    protected function createTestModelWithNoTransformer( array $attributes = [ ] ):Model
    {
        $model = new class extends Model
        {
            protected $fillable = [ 'name', 'price', 'is_rotten' ];
            protected $table = 'fruits';
        };

        return $this->storeModel( $model, $attributes );
    }

    /**
     * Creates a new adjustable model with a null transformer for testing purposes.
     *
     * @param  array $attributes
     * @return Model
     */
    protected function createTestModelWithNullTransformer( array $attributes = [ ] ):Model
    {
        $model = new class extends Model implements Transformable
        {
            protected $fillable = [ 'name', 'price', 'is_rotten' ];
            protected $table = 'fruits';

            public static function transformer()
            {
                return null;
            }
        };

        return $this->storeModel( $model, $attributes );
    }

    /**
     * Stores an actual instance of an adjustable model to the database.
     *
     * @param  Model $model
     * @param  array $attributes
     * @return Model
     */
    protected function storeModel( Model $model, array $attributes = [ ] ):Model
    {
        return $model->create( array_merge( [
            'name' => 'Mango',
            'price' => 10,
            'is_rotten' => false
        ], $attributes ) );
    }

    /**
     * Create a mock of the responder and binds it to the service container.
     *
     * @return \Mockery\MockInterface
     */
    protected function mockResponder()
    {
        $responder = Mockery::mock( Responder::class );

        $this->app->instance( Responder::class, $responder );

        return $responder;
    }

    /**
     * Create a mock of Laravel's translator and binds it to the service container.
     *
     * @return \Mockery\MockInterface
     */
    protected function mockTranslator()
    {
        $translator = Mockery::mock( Translator::class );

        $this->app->loadDeferredProvider( 'translator' );
        $this->app->instance( 'translator', $translator );

        return $translator;
    }
}