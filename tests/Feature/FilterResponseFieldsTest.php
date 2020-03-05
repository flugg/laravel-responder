<?php

namespace Flugg\Responder\Tests\Feature;

use Flugg\Responder\Tests\BasicProductTransformer;
use Flugg\Responder\Tests\ProductTransformer;
use Flugg\Responder\Tests\ProductWithClosureTransformer;
use Flugg\Responder\Tests\ProductWithIncludeMethodTransformer;
use Flugg\Responder\Tests\ProductWithShipmentsAutoloadedTransformer;
use Flugg\Responder\Tests\ProductWithShipmentsWhitelistedTransformer;
use Flugg\Responder\Tests\ProductWithTransformerClass;
use Flugg\Responder\Tests\ProductWithTransformerClassName;
use Flugg\Responder\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Mockery;

/**
 * Feature tests asserting that you can filter response fields of success responses.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class FilterResponseFieldsTest extends TestCase
{
    /**
     * Assert that you can filter fields with the sparse fieldset feature.
     */
    public function testItFiltersFields()
    {
        $response = responder()->success($this->product)->only('name')->respond();

        $this->assertEquals($this->responseData(Arr::only($this->product->toArray(), ['name'])), $response->getData(true));
    }

    /**
     * Assert that you can filter fields on relationships.
     */
    public function testItFiltersFieldsOfRelations()
    {
        $response = responder()->success($this->product, ProductTransformer::class)->with('shipments')->only([
                'products' => ['name'],
                'shipments' => ['id'],
            ])->respond();

        $this->assertEquals($this->responseData(array_merge(Arr::only($this->product->toArray(), ['name']), [
            'shipments' => [Arr::only($this->shipment->toArray(), ['id'])],
        ])), $response->getData(true));
    }

    /**
     * Assert that you can filter fields on nested relationships.
     */
    public function testItFiltersFieldsOfNestedRelations()
    {
        $response = responder()
            ->success($this->product, ProductTransformer::class)
            ->with('shipments', 'orders.customer')
            ->only([
                'products' => ['name'],
                'shipments' => ['id'],
                'customer' => ['name'],
            ])
            ->respond();

        $this->assertEquals($this->responseData(array_merge(Arr::only($this->product->toArray(), ['name']), [
            'shipments' => [Arr::only($this->shipment->toArray(), ['id'])],
            'orders' => [
                array_merge($this->order->toArray(), [
                    'customer' => Arr::only($this->customer->toArray(), ['name']),
                ]),
            ],
        ])), $response->getData(true));
    }

    /**
     * Assert that you can filter response fields using the configured query string parameter.
     */
    public function testFieldFieldsWithQueryStringParameter()
    {
        $this->app->instance(Request::class, $request = Mockery::mock(Request::class));
        $request->shouldReceive('input')->with('only', [])->andReturn([
            'products' => ['name'],
            'shipments' => ['id'],
            'customer' => ['name'],
        ]);
        $request->shouldReceive('input')->with('with', [])->andReturn([]);
        $request->shouldReceive('query')->andReturn([]);

        $response = responder()
            ->success($this->product, ProductTransformer::class)
            ->with('shipments', 'orders.customer')
            ->respond();

        $this->assertEquals($this->responseData(array_merge(Arr::only($this->product->toArray(), ['name']), [
            'shipments' => [Arr::only($this->shipment->toArray(), ['id'])],
            'orders' => [
                array_merge($this->order->toArray(), [
                    'customer' => Arr::only($this->customer->toArray(), ['name']),
                ]),
            ],
        ])), $response->getData(true));
    }
}