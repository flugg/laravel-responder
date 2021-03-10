<?php

namespace Flugg\Responder\Tests\Feature;

use Flugg\Responder\Tests\FeatureTestCase;
use Flugg\Responder\Tests\Product;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/**
 * Feature tests asserting that you can create success responses.
 */
class CreateSuccessResponseTest extends FeatureTestCase
{
    /**
     * Assert that you can create success responses without [respond].
     */
    public function testCreateSuccessResponseWithoutRespondMethod()
    {
        Route::get('/', function () {
            return responder()->success();
        });

        $response = $this->json('get', '/');

        $response->assertOk()->assertExactJson(['data' => null]);
    }

    /**
     * Assert that you can create success responses with [respond].
     */
    public function testCreateSuccessResponseWithRespondMethod()
    {
        Route::get('/', function () {
            return responder()->success()->respond();
        });

        $response = $this->json('get', '/');

        $response->assertOk()->assertExactJson(['data' => null]);
    }

    /**
     * Assert that you can create success responses with status code and headers.
     */
    public function testCreateSuccessResponseWithStatusCodeAndHeaders()
    {
        Route::get('/', function () {
            return responder()->success()->respond(201, ['x-foo' => 1]);
        });

        $response = $this->json('get', '/');

        $response->assertStatus(201)->assertHeader('x-foo', 1)->assertExactJson(['data' => null]);
    }

    /**
     * Assert that you can create success responses with primitives.
     */
    public function testCreateSuccessResponseWithPrimitive()
    {
        foreach ([true, 1.0, 1, 'foo', null] as $primitive) {
            Route::get('/', function () use ($primitive) {
                return responder()->success($primitive)->respond();
            });

            $response = $this->json('get', '/');

            $response->assertOk()->assertExactJson(['data' => $primitive]);
        }
    }

    /**
     * Assert that you can create success responses with arrays.
     */
    public function testCreateSuccessResponseWithArray()
    {
        $data = ['foo' => 1];
        Route::get('/', function () use ($data) {
            return responder()->success($data)->respond();
        });

        $response = $this->json('get', '/');

        $response->assertOk()->assertExactJson(['data' => $data]);
    }

    /**
     * Assert that you can create success responses with collections.
     */
    public function testCreateSuccessResponseWithCollection()
    {
        $data = Collection::make(['foo' => 1]);
        Route::get('/', function () use ($data) {
            return responder()->success($data)->respond();
        });

        $response = $this->json('get', '/');

        $response->assertOk()->assertExactJson(['data' => $data->toArray()]);
    }

    /**
     * Assert that you can create success responses with arrayables.
     */
    public function testCreateSuccessResponseWithArrayble()
    {
        $arrayable = new class() implements Arrayable {
            public function toArray()
            {
                return ['foo' => 1];
            }
        };
        Route::get('/', function () use ($arrayable) {
            return responder()->success($arrayable)->respond();
        });

        $response = $this->json('get', '/');

        $response->assertOk()->assertExactJson(['data' => $arrayable->toArray()]);
    }

    /**
     * Assert that you can create success responses with Eloquent models.
     */
    public function testCreateSuccessResponseWithEloquentModel()
    {
        $product = Product::factory()->create();
        Route::get('/', function () use ($product) {
            return responder()->success($product)->respond();
        });

        $response = $this->json('get', '/');

        $response->assertStatus(201)->assertExactJson(['data' => $product->toArray()]);
    }

    /**
     * Assert that you can create success responses with Eloquent collections.
     */
    public function testCreateSuccessResponseWithEloquentCollection()
    {
        $products = Product::factory()->count(3)->create();
        Route::get('/', function () use ($products) {
            return responder()->success($products)->respond();
        });

        $response = $this->json('get', '/');

        $response->assertOk()->assertExactJson(['data' => $products->toArray()]);
    }

    /**
     * Assert that you can create success responses with paginators.
     */
    public function testCreateSuccessResponseWithPaginator()
    {
        $products = Product::factory()->count(10)->create();
        $paginator = Product::paginate(5);
        $path = $this->app->make('request')->url();
        Route::get('/', function () use ($paginator) {
            return responder()->success($paginator)->respond();
        });

        $response = $this->json('get', '/');

        $response->assertOk()->assertExactJson([
            'data' => $paginator->getCollection()->toArray(),
            'pagination' => [
                'count' => $paginator->getCollection()->count(),
                'total' => $products->count(),
                'perPage' => $paginator->getCollection()->count(),
                'currentPage' => 1,
                'lastPage' => 2,
                'links' => [
                    'self' => "{$path}?page=1",
                    'first' => "{$path}?page=1",
                    'last' => "{$path}?page=2",
                    'next' => "{$path}?page=2",
                ],
            ],
        ]);
    }

    /**
     * Assert that you can create success responses with query builders.
     */
    public function testCreateSuccessResponseWithQueryBuilder()
    {
        $products = Product::factory()->count(3)->create();
        $query = DB::table('products');
        Route::get('/', function () use ($query) {
            return responder()->success($query)->respond();
        });

        $response = $this->json('get', '/');

        $response->assertOk()->assertExactJson(['data' => $products->map(function ($product) {
            return [
                'created_at' => $product->created_at->toDateTimeString(),
                'id' => (string) $product->id,
                'name' => $product->name,
                'updated_at' => $product->updated_at->toDateTimeString(),
            ];
        })]);
    }

    /**
     * Assert that you can create success responses with Eloquent query builders.
     */
    public function testCreateSuccessResponseWithEloquentQueryBuilder()
    {
        $products = Product::factory()->count(3)->create();
        $query = Product::get();
        Route::get('/', function () use ($query) {
            return responder()->success($query)->respond();
        });

        $response = $this->json('get', '/');

        $response->assertOk()->assertExactJson(['data' => $products->toArray()]);
    }

    /**
     * Assert that you can create success responses with Eloquent one-to-one relations.
     */
    public function testCreateSuccessResponseWithEloquentSingularRelation()
    {
        $this->markTestSkipped('TODO');
    }

    /**
     * Assert that you can create success responses with Eloquent one-to-many or many-to-many relations.
     */
    public function testCreateSuccessResponseWithEloquentPluralRelation()
    {
        $this->markTestSkipped('TODO');
    }

    /**
     * Assert that you can create success responses with other data using custom normalizers.
     */
    public function testCreateSuccessResponseWithCustomNormalizer()
    {
        $this->markTestSkipped('TODO');
    }

    /**
     * Assert that you can create success responses with a paginator explicitly attached using [paginator].
     */
    public function testCreateSuccessResponseWithAnAttachedPaginator()
    {
        $this->markTestSkipped('TODO');
    }

    /**
     * Assert that you can create success responses with a cursor paginator explicitly attached using [cursor].
     */
    public function testCreateSuccessResponseWithAnAttachedCursor()
    {
        $this->markTestSkipped('TODO');
    }
}
