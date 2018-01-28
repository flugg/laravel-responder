<?php

namespace Flugg\Responder\Tests\Feature;

use Carbon\Carbon;
use Flugg\Responder\Tests\OrderTransformer;
use Flugg\Responder\Tests\Product;
use Flugg\Responder\Tests\ProductTransformer;
use Flugg\Responder\Tests\TestCase;
use Illuminate\Http\Request;
use Mockery;

/**
 * Feature tests asserting that you can include relationships with success responses.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class IncludeRelationTest extends TestCase
{
    /**
     * Assert that you can include associated resources.
     */
    public function testIncludeRelations()
    {
        $response = responder()->success($this->product, ProductTransformer::class)->with('shipments')->respond();

        $this->assertEquals($this->responseData(array_merge($this->product->toArray(), [
            'shipments' => [$this->shipment->toArray()],
        ])), $response->getData(true));
    }

    /**
     * Assert that it for safety reasons wont include relations if no transformer is provided.
     */
    public function testItWontIncludeRelationsWithoutATransformerSpecified()
    {
        $response = responder()->success($this->product)->with('shipments')->respond();

        $this->assertEquals($this->responseData($this->product->toArray()), $response->getData(true));
    }

    /**
     * Assert that you cannot include relationships not specified in the $relations property
     * of a transformer class when given a dedicated transformer.
     */
    public function testItOnlyIncludesWhitelistedRelations()
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
     * Assert that you can include associated resources including a query constraint.
     */
    public function testIncludeRelationsWithQueryConstraints()
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
    public function testIncludeNestedRelations()
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
     * Assert that you can include associated resources using the configured query string parameter.
     */
    public function testIncludeRelationsWithQueryStringParameter()
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
    public function testIncludeRelationsByDefault()
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
     * Assert that you can exclude relationships defined as autoloading in the transformer.
     */
    public function testExcludeDefaultRelations()
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
    public function testItEagerLoadsRelations()
    {
        $product = Mockery::mock($this->product);

        responder()
            ->success($product, ProductWithShipmentsWhitelistedTransformer::class)
            ->with('shipments', 'orders')
            ->respond();

        $product->shouldHaveReceived('load')->with(Mockery::on(function ($argument) {
            return array_has($argument, 'shipments') && count($argument) === 1;
        }))->once();
    }

    /**
     * Assert that it loads relations from an "include" method on the transformer if it's
     * defined.
     */
    public function testItIncludesRelationsFromIncludeMethod()
    {
        $product = Mockery::mock($this->product);

        $response = responder()
            ->success($product, ProductWithIncludeMethodTransformer::class)
            ->with('shipments', 'orders')
            ->respond();

        $product->shouldHaveReceived('load')->with(Mockery::on(function ($argument) {
            return array_has($argument, 'orders') && count($argument) === 1;
        }))->once();
        $this->assertEquals($this->responseData(array_merge($this->product->fresh()->toArray(), [
            'shipments' => [$this->shipment->toArray()],
            'orders' => [$this->order->toArray()],
        ])), $response->getData(true));
    }

    /**
     * Assert that it loads relations with query constraints from a "load" method
     * on the transformer if it's defined.
     */
    public function testIncludeRelationsWithQueryConstraintsFromLoadMethod()
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
    public function testFilterRelationsWithFilterMethod()
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

class ProductWithShipmentsAutoloadedTransformer extends ProductTransformer
{
    protected $load = ['shipments', 'orders' => OrderWithCustomerAutoloadedTransformer::class];
}

class OrderWithCustomerAutoloadedTransformer extends OrderTransformer
{
    protected $load = ['customer'];
}

class ProductWithIncludeMethodTransformer extends ProductTransformer
{
    protected $relations = ['shipments', 'orders'];

    public function includeShipments(Product $product)
    {
        return $product->shipments;
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