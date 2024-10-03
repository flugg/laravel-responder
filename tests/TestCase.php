<?php

namespace Flugg\Responder\Tests;

use Flugg\Responder\Contracts\ResponseFactory;
use Flugg\Responder\Http\Responses\ErrorResponseBuilder;
use Flugg\Responder\Http\Responses\SuccessResponseBuilder;
use Flugg\Responder\Resources\ResourceBuilder;
use Flugg\Responder\ResponderServiceProvider;
use Flugg\Responder\TransformBuilder;
use Flugg\Responder\Transformers\Transformer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\JsonResponse;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use Orchestra\Testbench\TestCase as BaseTestCase;

/**
 * The base test case class, responsible for bootstrapping the testing environment.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
abstract class TestCase extends BaseTestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * A dummy product model.
     *
     * @var \Flugg\Responder\Tests\Product
     */
    protected $product;

    /**
     * A dummy shipment model.
     *
     * @var \Flugg\Responder\Tests\Product
     */
    protected $shipment;

    /**
     * A dummy customer model.
     *
     * @var \Flugg\Responder\Tests\Product
     */
    protected $customer;

    /**
     * A dummy order model.
     *
     * @var \Flugg\Responder\Tests\Product
     */
    protected $order;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->runTestMigrations();

        $this->product = Product::create()->fresh();
        $this->shipment = Shipment::create(['product_id' => $this->product->id])->fresh();
        $this->customer = Customer::create()->fresh();
        $this->order = Order::create(['product_id' => $this->product->id, 'customer_id' => $this->customer->id]);
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
            'database' => ':memory:',
        ]);
    }

    /**
     * Run migrations for tables used for testing purposes.
     *
     * @return void
     */
    private function runTestMigrations()
    {
        $schema = $this->app['db']->connection()->getSchemaBuilder();

        if (! $schema->hasTable('products')) {
            $schema->create('products', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name')->nullable();
                $table->timestamps();
            });
        }

        if (! $schema->hasTable('shipments')) {
            $schema->create('shipments', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('product_id');
                $table->timestamps();
            });
        }

        if (! $schema->hasTable('orders')) {
            $schema->create('orders', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('product_id');
                $table->unsignedInteger('customer_id');
                $table->timestamps();
            });
        }

        if (! $schema->hasTable('customers')) {
            $schema->create('customers', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name')->nullable();
                $table->timestamps();
            });
        }
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
            ResponderServiceProvider::class,
        ];
    }

    /**
     * Merge given data with the skeleton of a serialization using the default [SuccessSerializer].
     *
     * @param  null  $data
     * @param  array $meta
     * @param  int   $status
     * @return array
     */
    protected function responseData($data = null, $meta = [], $status = 200): array
    {
        return array_merge([
            'status' => $status,
            'success' => true,
            'data' => $data,
        ], $meta);
    }

    /**
     * Create a mock of a [Transformer] returning the data directly.
     *
     * @return \Mockery\MockInterface
     */
    protected function mockTransformer(): MockInterface
    {
        $transformer = Mockery::mock(Transformer::class);

        $transformer->shouldReceive('transform')->andReturnUsing(function ($data) {
            return $data;
        });

        return $transformer;
    }

    /**
     * Create a mock of a [TransformBuilder].
     *
     * @return \Mockery\MockInterface
     */
    protected function mockTransformBuilder(): MockInterface
    {
        $transformBuilder = Mockery::mock(TransformBuilder::class);

        $transformBuilder->shouldReceive('resource')->andReturnSelf();
        $transformBuilder->shouldReceive('meta')->andReturnSelf();
        $transformBuilder->shouldReceive('with')->andReturnSelf();
        $transformBuilder->shouldReceive('without')->andReturnSelf();
        $transformBuilder->shouldReceive('serializer')->andReturnSelf();

        return $transformBuilder;
    }

    /**
     * Create a mock of a [ResponseFactory]].
     *
     * @return \Mockery\MockInterface
     */
    protected function mockResponseFactory(): MockInterface
    {
        $responseFactory = Mockery::mock(ResponseFactory::class);

        $responseFactory->shouldReceive('make')->andReturnUsing(function ($data, $status, $headers) {
            return new JsonResponse($data, $status, $headers);
        });

        return $responseFactory;
    }

    /**
     * Create a mock of an [ErrorResponseBuilder].
     *
     * @return \Mockery\MockInterface
     */
    protected function mockErrorResponseBuilder(): MockInterface
    {
        $responseBuilder = Mockery::mock(ErrorResponseBuilder::class);

        $responseBuilder->shouldReceive('error')->andReturnSelf();
        $responseBuilder->shouldReceive('data')->andReturnSelf();

        return $responseBuilder;
    }

    /**
     * Create a mock of a [SuccessResponseBuilder].
     *
     * @return \Mockery\MockInterface
     */
    protected function mockSuccessResponseBuilder(): MockInterface
    {
        $responseBuilder = Mockery::mock(SuccessResponseBuilder::class);

        $responseBuilder->shouldReceive('transform')->andReturnSelf();
        $responseBuilder->shouldReceive('meta')->andReturnSelf();

        return $responseBuilder;
    }

    /**
     * Create a mock of a Fractal [Manager].
     *
     * @return \Mockery\MockInterface
     */
    protected function mockFractalManager(): MockInterface
    {
        $responseBuilder = Mockery::mock(Manager::class);

        $responseBuilder->shouldReceive('setSerializer')->andReturnSelf()->byDefault();
        $responseBuilder->shouldReceive('parseIncludes')->andReturnSelf()->byDefault();
        $responseBuilder->shouldReceive('parseExcludes')->andReturnSelf()->byDefault();
        $responseBuilder->shouldReceive('parseFieldsets')->andReturnSelf()->byDefault();

        return $responseBuilder;
    }

    /**
     * Create a mock of a [ResourceInterface].
     *
     * @param  string|null $className
     * @return \Mockery\MockInterface
     */
    protected function mockResource(string $className = null): MockInterface
    {
        $resource = Mockery::mock($className ?: Collection::class);

        $resource->shouldReceive('getData')->andReturnNull()->byDefault();
        $resource->shouldReceive('getTransformer')->andReturnNull()->byDefault();
        $resource->shouldReceive('setMeta')->andReturnSelf()->byDefault();
        $resource->shouldReceive('setCursor')->andReturnSelf()->byDefault();
        $resource->shouldReceive('setPaginator')->andReturnSelf()->byDefault();

        return $resource;
    }
}

class Product extends Model
{
    protected $guarded = [];
    protected $table = 'products';

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}

class Shipment extends Model
{
    protected $guarded = [];
    protected $table = 'shipments';
    protected $casts = [
        'product_id' => 'int',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

class Order extends Model
{
    protected $guarded = [];
    protected $table = 'orders';
    protected $casts = [
        'product_id' => 'int',
        'customer_id' => 'int',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}

class Customer extends Model
{
    protected $guarded = [];
    protected $table = 'customers';

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}

class ProductTransformer extends Transformer
{
    protected $relations = [
        'shipments' => ShipmentTransformer::class,
        'orders' => OrderTransformer::class,
    ];

    public function transform(Product $product)
    {
        return $product->fresh()->toArray();
    }
}

class ShipmentTransformer extends Transformer
{
    public function transform(Shipment $shipment)
    {
        return $shipment->fresh()->toArray();
    }
}

class OrderTransformer extends Transformer
{
    protected $relations = [
        'customer' => CustomerTransformer::class,
    ];

    public function transform(Order $order)
    {
        return $order->fresh()->toArray();
    }
}

class CustomerTransformer extends Transformer
{
    public function transform(Customer $customer)
    {
        return $customer->fresh()->toArray();
    }
}