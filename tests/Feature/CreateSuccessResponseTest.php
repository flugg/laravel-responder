<?php

namespace Flugg\Responder\Tests\Feature;

use Flugg\Responder\Tests\Product;
use Flugg\Responder\Tests\TestCase;
use League\Fractal\Pagination\Cursor;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use stdClass;

/**
 * Feature tests asserting that you can create success responses.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
final class CreateSuccessResponseTest extends TestCase
{
    /**
     * Assert that you can create success responses with no response data.
     */
    public function testCreateResponsesWithoutData(): void
    {
        $response = responder()->success()->respond();

        $this->assertEquals($this->responseData(null), $response->getData(true));
    }

    /**
     * Assert that you can create success responses with response data.
     */
    public function testCreateResponsesWithBasicArray(): void
    {
        $response = responder()->success($data = ['foo', 'bar'])->respond();

        $this->assertEquals($this->responseData($data), $response->getData(true));
    }

    /**
     * Assert that you can create success responses with associative arrays.
     */
    public function testCreateResponsesWithAssociativeArray(): void
    {
        $response = responder()->success($data = [
            'foo' => 123,
            'bar' => 456,
        ])->respond();

        $this->assertEquals($this->responseData($data), $response->getData(true));
    }

    /**
     * Assert that you can create success responses with associative arrays containing objects.
     */
    public function testCreateResponsesWithAssociativeArrayContainingObjects(): void
    {
        $response = responder()->success($data = [
            'foo' => new stdClass(),
            'bar' => new stdClass(),
        ])->respond();

        $this->assertEquals($this->responseData(array_map(function ($item) {
            return (array) $item;
        }, $data)), $response->getData(true));
    }

    /**
     * Assert that you can create success responses with model as data.
     */
    public function testCreateResponsesWithModel(): void
    {
        $response = responder()->success($this->product)->respond();

        $this->assertEquals($this->responseData($this->product->toArray()), $response->getData(true));
    }

    /**
     * Assert that you can create success responses with an array of models as data.
     */
    public function testCreateResponsesWithArrayOfModels(): void
    {
        $response = responder()->success($products = [$this->product])->respond();

        $this->assertEquals($this->responseData(array_map(function ($product) {
            return $product->toArray();
        }, $products)), $response->getData(true));
    }

    /**
     * Assert that you can create success responses with a collection of models as data.
     */
    public function testCreateResponsesWithCollectionOfModels(): void
    {
        $response = responder()->success($products = collect([$this->product]))->respond();

        $this->assertEquals($this->responseData($products->toArray()), $response->getData(true));
    }

    /**
     * Assert that you can create success responses with a query builder as data.
     */
    public function testCreateResponsesWithQueryBuilder(): void
    {
        $response = responder()->success($this->product->newQuery())->respond();

        $this->assertEquals($this->responseData([$this->product->toArray()]), $response->getData(true));
    }

    /**
     * Assert that you can create success responses with a relation query as data.
     */
    public function testCreateResponsesWithRelationQuery(): void
    {
        $response = responder()->success($this->product->shipments())->respond();

        $this->assertEquals($this->responseData([$this->shipment->toArray()]), $response->getData(true));
    }

    /**
     * Assert that you can create success responses with a singular relation query as data.
     */
    public function testCreateResponsesWithSingularRelationQuery(): void
    {
        $response = responder()->success($this->shipment->product())->respond();

        $this->assertEquals($this->responseData($this->product->toArray()), $response->getData(true));
    }

    /**
     * Assert that you can create success responses with paginator as data.
     */
    public function testCreateResponsesWithPagination(): void
    {
        $response = responder()->success($this->product->newQuery()->paginate())->respond();

        $this->assertEquals($this->responseData([$this->product->toArray()], [
            'pagination' => [
                'count' => 1,
                'total' => 1,
                'perPage' => 15,
                'currentPage' => 1,
                'totalPages' => 1,
                'links' => [],
            ],
        ]), $response->getData(true));
    }

    /**
     * Assert that you can create success responses with pagination where we apply the
     * pagination and data seperately.
     */
    public function testCreateResponsesWithExplicitPaginator(): void
    {
        $adapter = new IlluminatePaginatorAdapter($this->product->newQuery()->paginate());

        $response = responder()->success([$this->product])->paginator($adapter)->respond();

        $this->assertEquals($this->responseData([$this->product->toArray()], [
            'pagination' => [
                'count' => 1,
                'total' => 1,
                'perPage' => 15,
                'currentPage' => 1,
                'totalPages' => 1,
                'links' => [],
            ],
        ]), $response->getData(true));
    }

    /**
     * Assert that you can create success responses with cursor pagination.
     */
    public function testCreateResponsesWithCursorPagination(): void
    {
        $adapter = new Cursor($this->product->id, null, null, $count = Product::count());

        $response = responder()->success([$this->product])->cursor($adapter)->respond();

        $this->assertEquals($this->responseData([$this->product->toArray()], [
            'cursor' => [
                'current' => $this->product->id,
                'previous' => null,
                'next' => null,
                'count' => $count,
            ],
        ]), $response->getData(true));
    }

    /**
     * Assert that you can add meta data to the response.
     */
    public function testAddMetaData(): void
    {
        $response = responder()->success()->meta($meta = ['foo' => 123])->respond();

        $this->assertEquals(array_merge($this->responseData(), $meta), $response->getData(true));
    }
}
