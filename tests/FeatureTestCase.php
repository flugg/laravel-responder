<?php

namespace Flugg\Responder\Tests;

use Flugg\Responder\ResponderServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase;

/**
 * Abstract test case for bootstrapping the environment for the feature suite.
 */
abstract class FeatureTestCase extends TestCase
{
    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
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
        return [ResponderServiceProvider::class];
    }

    /**
     * Define database migrations.
     *
     * @return void
     */
    protected function defineDatabaseMigrations()
    {
        $schema = $this->app->make('db')->connection()->getSchemaBuilder();

        $schema->create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->timestamps();
        });
    }
}

class Product extends Model
{
    protected $guarded = [];

    protected static function factory()
    {
        return ProductFactory::new();
    }
}

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        return ['name' => $this->faker->name];
    }
}
