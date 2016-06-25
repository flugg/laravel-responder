<?php

namespace Mangopixel\Adjuster\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use Mangopixel\Adjuster\ResponderServiceProvider;
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
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

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
     * Creates a new adjustable model for testing purposes.
     *
     * @param  array $attributes
     * @return Model
     */
    protected function createTestModel( array $attributes = [ ] ):Model
    {
        $model = new class extends Model
        {
            protected $fillable = [ 'name', 'price' ];
            protected $table = 'fruits';
        };

        return $this->storeAdjustableModel( $model, $attributes );
    }

    /**
     * Stores an actual instance of an adjustable model to the database.
     *
     * @param  Model $model
     * @param  array $attributes
     * @return Model
     */
    protected function storeAdjustableModel( Model $model, array $attributes = [ ] ):Model
    {
        return $model->create( array_merge( [
            'name' => 'Mango',
            'price' => 10
        ], $attributes ) );
    }
}