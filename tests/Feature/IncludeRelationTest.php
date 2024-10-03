<?php

namespace Flugg\Responder\Tests\Feature;

use Carbon\Carbon;
use Flugg\Responder\Tests\OrderTransformer;
use Flugg\Responder\Tests\Product;
use Flugg\Responder\Tests\ProductTransformer;
use Flugg\Responder\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Mockery;

/**
 * Feature tests asserting that you can include relationships with success responses.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
final class IncludeRelationTest extends TestCase
{
    /**
     * Assert that you can include associated resources.
     */
    public function testIncludeRelations(): void
    {
        $response = responder()->success($this->product, ProductTransformer::class)->with('shipments')->respond();

        $this->assertEquals($this->responseData(array_merge($this->product->toArray(), [
            'shipments' => [$this->shipment->toArray()],
        ])), $response->getData(true));
    }

    /**
     * Assert that it for safety reasons wont include relations if no transformer is provided.
     */
    public function testItWontIncludeRelationsWithoutATransformerSpecified(): void
    {
        $response = responder()->success($this->product)->with('shipments')->respond();

        $this->assertEquals($this->responseData($this->product->toArray()), $response->getData(true));
    }

    /**
     * Assert that you cannot include relationships not specified in the $relations property
     * of a transformer class when given a dedicated transformer.
     */
    public function testItOnlyIncludesWhitelistedRelations(): void
    {
        $response = responder()
            ->success($this->product, ProductWithShipmentsWhitelistedTransformer::class)
            ->with('shipments', 'orders.customer', 'invalid')
            ->respond();

        $this->assertEquals($this->responseData(array_merge($this->product->fresh()->toArray(), [
            'shipments' => [$this->shipment->toArray()],
        ])), $response->getData(true));
    }

    /**
     * Assert that it doesn't end up with a circular dependency when extracting whitelisted
     * relationships from the transformers.
     */
    public function testIncludeCircularRelations(): void
    {
        $response = responder()
            ->success($this->product, ProductWithOrdersWhitelistedTransformer::class)
            ->with('orders.product')
            ->respond();

        $this->assertEquals($this->responseData(array_merge($this->product->fresh()->toArray(), [
            'orders' => [
                array_merge($this->order->toArray(), [
                    'product' => $this->product->fresh()->toArray(),
                ]),
            ],
        ])), $response->getData(true));
    }

    /**
     * Assert that you can include associated resources including a query constraint.
     */
    public function testIncludeRelationsWithQueryConstraints(): void
    {
        $shipment = $this->product->shipments()->create(['created_at' => Carbon::tomorrow()]);

        $response = responder()->success($this->product, ProductTransformer::class)->with([
            'shipments' => function ($query) {
                $query->where('created_at', '>', Carbon::now());
            },
        ])->respond();

        $this->assertEquals($this->responseData(array_merge($this->product->toArray(), [
            'shipments' => [$shipment->toArray()],
        ])), $response->getData(true));
    }

    /**
     * Assert that you can include associated resources including their nested resources again.
     */
    public function testIncludeNestedRelations(): void
    {
        $response = responder()
            ->success($this->product, ProductTransformer::class)
            ->with('shipments', 'orders.customer')
            ->respond();

        $this->assertEquals($this->responseData(array_merge($this->product->toArray(), [
            'shipments' => [$this->shipment->toArray()],
            'orders' => [
                array_merge($this->order->toArray(), [
                    'customer' => $this->customer->toArray(),
                ]),
            ],
        ])), $response->getData(true));
    }

    /**
     * Assert that when you include a nested relation with a query string constraint, that's
     * not applied for the parent relation.
     */
    public function testIncludeNestedRelationsWithQueryConstraints(): void
    {
        $product = Mockery::mock($this->product);

        responder()->success($product, ProductTransformer::class)->with([
            'orders.customer' => function ($query) {
                $query->where('name', 'foo');
            },
        ])->respond();

        $product->shouldHaveReceived('load')->with(Mockery::on(function ($argument) {
            return Arr::has($argument, 'orders') && is_callable($argument['orders']) && count($argument) === 2;
        }))->once();
    }

    /**
     * Assert that you can include associated resources using the configured query string parameter.
     */
    public function testIncludeRelationsWithQueryStringParameter(): void
    {
        $this->app->instance(Request::class, $request = Mockery::mock(Request::class));
        $request->shouldReceive('input')->with('with', [])->andReturn('shipments,orders.customer');
        $request->shouldReceive('input')->with('only', [])->andReturn([]);
        $request->shouldReceive('query')->andReturn([]);

        $response = responder()->success($this->product, ProductTransformer::class)->respond();

        $this->assertEquals($this->responseData(array_merge($this->product->toArray(), [
            'shipments' => [$this->shipment->toArray()],
            'orders' => [
                array_merge($this->order->toArray(), [
                    'customer' => $this->customer->toArray(),
                ]),
            ],
        ])), $response->getData(true));
    }

    /**
     * Assert that it automatically includes relationships declared in the $load property
     * of a transformer class when given a dedicated transformer.
     */
    public function testIncludeRelationsByDefault(): void
    {
        $response = responder()->success($this->product, ProductWithShipmentsAutoloadedTransformer::class)->respond();

        $this->assertEquals($this->responseData(array_merge($this->product->toArray(), [
            'shipments' => [$this->shipment->toArray()],
            'orders' => [
                array_merge($this->order->toArray(), [
                    'customer' => $this->customer->toArray(),
                ]),
            ],
        ])), $response->getData(true));
    }

    /**
     * Assert that it automatically includes relationships declared in the $load property
     * of a transformer class resolved from a nested transformer.
     */
    public function testIncludeRelationsByDefaultFromNestedTransformer(): void
    {
        $response = responder()
            ->success($this->product, ProductWithOrdersWhitelistedAndCustomerAutoloadedTransformer::class)
            ->with('orders')
            ->respond();

        $this->assertEquals($this->responseData(array_merge($this->product->toArray(), [
            'orders' => [
                array_merge($this->order->toArray(), [
                    'customer' => $this->customer->toArray(),
                ]),
            ],
        ])), $response->getData(true));
    }

    /**
     * Assert that you can exclude relationships defined as autoloading in the transformer.
     */
    public function testExcludeDefaultRelations(): void
    {
        $response = responder()
            ->success($this->product, ProductWithShipmentsAutoloadedTransformer::class)
            ->without('shipments', 'orders.customer')
            ->respond();

        $this->assertEquals($this->responseData(array_merge($this->product->toArray(), [
            'orders' => [$this->order->toArray()],
        ])), $response->getData(true));
    }

    /**
     * Assert that it automatically eager loads whitelisted and requested relationship.
     */
    public function testItEagerLoadsRelations(): void
    {
        $product = Mockery::mock($this->product);

        responder()
            ->success($product, ProductWithShipmentsWhitelistedTransformer::class)
            ->with('shipments', 'orders')
            ->respond();

        $product->shouldHaveReceived('load')->with(Mockery::on(function ($argument) {
            return Arr::has($argument, 'shipments') && count($argument) === 1;
        }))->once();
    }

    /**
     * Assert that it converts snake cased relations to camel case before loading them
     * from the model.
     */
    public function testItConvertsSnakeCasedRelationsToCamelCase(): void
    {
        $product = Mockery::mock($this->product);
        $product->shouldReceive('load')->andReturnSelf();

        responder()
            ->success($product, ProductWithSnakeCasedRelationsTransformer::class)
            ->with('whitelisted_shipments', 'default_orders')
            ->respond();

        $product->shouldHaveReceived('load')->with(Mockery::on(function ($argument) {
            return Arr::has($argument, ['whitelistedShipments', 'defaultOrders']) && count($argument) === 2;
        }))->once();
    }

    /**
     * Assert that it would not converts snake cased relations to camel case before loading them
     * from the model if the config use_camel_case_relations set to false
     */
    public function testItNotConvertsSnakeCasedRelationsToCamelCase(): void
    {
        config(['responder.use_camel_case_relations' => false]);
        $product = Mockery::mock($this->product);
        $product->shouldReceive('load')->andReturnSelf();

        responder()
            ->success($product, ProductWithSnakeCasedRelationsTransformer::class)
            ->with('whitelisted_shipments', 'default_orders')
            ->respond();

        $product->shouldHaveReceived('load')->with(Mockery::on(function ($argument) {
            return Arr::has($argument, ['whitelisted_shipments', 'default_orders']) && count($argument) === 2;
        }))->once();
    }

    /**
     * Assert that it loads relations from an "include" method on the transformer if it's
     * defined.
     */
    public function testItIncludesRelationsFromIncludeMethod(): void
    {
        $product = Mockery::mock($this->product);

        $response = responder()
            ->success($product, ProductWithIncludeMethodTransformer::class)
            ->with('shipments', 'orders')
            ->respond();

        $product->shouldHaveReceived('load')->with(Mockery::on(function ($argument) {
            return Arr::has($argument, 'orders') && count($argument) === 1;
        }))->once();
        $this->assertEquals($this->responseData(array_merge($this->product->fresh()->toArray(), [
            'shipments' => [$this->shipment->toArray()],
            'orders' => [$this->order->toArray()],
        ])), $response->getData(true));
    }

    /**
     * Assert that it loads relations from an "include" method on the transformer if it's
     * defined.
     */
    public function testItSendsParametersToTheIncludeMethod(): void
    {
        $response = responder()
            ->success($this->product, ProductWithIncludeMethodAndParametersTransformer::class)
            ->with('shipments:product(foo|bar)')
            ->respond();

        $this->assertEquals($this->responseData(array_merge($this->product->fresh()->toArray(), [
            'shipments' => ['foo', 'bar'],
        ])), $response->getData(true));
    }

    /**
     * Assert that it loads relations with query constraints from a "load" method
     * on the transformer if it's defined.
     */
    public function testIncludeRelationsWithQueryConstraintsFromLoadMethod(): void
    {
        $shipment = $this->product->shipments()->create(['created_at' => Carbon::tomorrow()]);

        $response = responder()
            ->success($this->product, ProductWithLoadMethodTransformer::class)
            ->with('shipments')
            ->respond();

        $this->assertEquals($this->responseData(array_merge($this->product->toArray(), [
            'shipments' => [$shipment->toArray()],
        ])), $response->getData(true));
    }

    /**
     * Assert that you can filter relations after they've been loaded with a "filter"
     * method on the transformer.
     */
    public function testFilterRelationsWithFilterMethod(): void
    {
        $shipment = $this->product->shipments()->create(['created_at' => Carbon::tomorrow()]);

        $response = responder()
            ->success($this->product, ProductWithFilterMethodTransformer::class)
            ->with('shipments')
            ->respond();

        $this->assertEquals($this->responseData(array_merge($this->product->toArray(), [
            'shipments' => [$shipment->toArray()],
        ])), $response->getData(true));
    }
}

class ProductWithShipmentsWhitelistedTransformer extends ProductTransformer
{
    protected $relations = ['shipments'];
}

class ProductWithOrdersWhitelistedTransformer extends ProductTransformer
{
    protected $relations = ['orders' => OrderWithProductWhitelistedTransformer::class];
}

class ProductWithOrdersWhitelistedAndCustomerAutoloadedTransformer extends ProductTransformer
{
    protected $relations = ['orders' => OrderWithCustomerAutoloadedTransformer::class];
}

class ProductWithShipmentsAutoloadedTransformer extends ProductTransformer
{
    protected $load = ['shipments', 'orders' => OrderWithCustomerAutoloadedTransformer::class];
}

class OrderWithProductWhitelistedTransformer extends OrderTransformer
{
    protected $relations = ['product' => ProductWithOrdersWhitelistedTransformer::class];
}

class OrderWithCustomerAutoloadedTransformer extends OrderTransformer
{
    protected $load = ['customer'];
}

class ProductWithSnakeCasedRelationsTransformer extends ProductTransformer
{
    protected $relations = ['whitelisted_shipments'];
    protected $load = ['default_orders'];
}

class ProductWithIncludeMethodTransformer extends ProductTransformer
{
    protected $relations = ['shipments', 'orders'];

    public function includeShipments(Product $product)
    {
        return $product->shipments;
    }
}

class ProductWithIncludeMethodAndParametersTransformer extends ProductTransformer
{
    protected $relations = ['shipments', 'orders'];

    public function includeShipments(Product $product, Collection $parameters)
    {
        return $parameters->get('product');
    }
}

class ProductWithLoadMethodTransformer extends ProductTransformer
{
    protected $relations = ['shipments', 'orders'];

    public function loadShipments($query)
    {
        return $query->where('created_at', '>', Carbon::now());
    }
}

class ProductWithFilterMethodTransformer extends ProductTransformer
{
    protected $relations = ['shipments', 'orders'];

    public function filterShipments($shipments)
    {
        return $shipments->filter(function ($shipment) {
            return $shipment->created_at > Carbon::now();
        });
    }
}
