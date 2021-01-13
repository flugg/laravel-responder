<?php

namespace Flugg\Responder\Tests\Feature;

use Flugg\Responder\Tests\FeatureTestCase;
use Illuminate\Support\Facades\Route;

/**
 * Feature tests asserting that you can create success responses.
 */
class CreateSuccessResponseTest extends FeatureTestCase
{
    /**
     * Assert that you can create success responses with no data.
     */
    public function testCreateSuccessResponseWithoutData()
    {
        Route::get('/', function () {
            return responder()->success()->respond();
        });

        $response = $this->json('get', '/');

        $response->assertOk()->assertExactJson(['data' => null]);
    }

    /**
     * Assert that you can create success responses with primitive.
     */
    public function testCreateSuccessResponseWithPrimitive()
    {
        foreach ([true, 1.0, 1, 'foo'] as $primitive) {
            Route::get('/', function () use ($primitive) {
                return responder()->success($primitive)->respond();
            });

            $response = $this->json('get', '/');

            $response->assertOk()->assertExactJson(['data' => $primitive]);
        }
    }

    /**
     * Assert that you can create success responses with no data or formatter.
     */
    public function testCreateSuccessResponseWithoutDataAndNoFormatter()
    {
        config()->set('responder.formatter', null);
        Route::get('/', function () {
            return responder()->success()->respond();
        });

        $response = $this->json('get', '/');

        $response->assertOk()->assertExactJson([]);
    }

    /**
     * Assert that you can create success responses with no data and no formatter.
     */
    public function testCreateSuccessResponseWithPrimitiveAndNoFormatter()
    {
        foreach ([true, 1.0, 1, 'foo'] as $primitive) {
            config()->set('responder.formatter', null);
            Route::get('/', function () use ($primitive) {
                return responder()->success($primitive)->respond();
            });

            $response = $this->json('get', '/');

            $response->assertOk()->assertExactJson([123]);
        }
    }
}
