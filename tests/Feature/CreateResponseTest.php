<?php

namespace Flugg\Responder\Tests\Feature;

use Flugg\Responder\Contracts\Responder;
use Flugg\Responder\Facades\Responder as ResponderFacade;
use Flugg\Responder\Http\MakesResponses;
use Flugg\Responder\Tests\TestCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

/**
 * Feature tests asserting that you can create responses in various ways.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class CreateResponseTest extends TestCase
{
    /**
     * Assert that you can create responses using an injected responder service.
     */
    public function testCreateResponses()
    {
        $responder = resolve(Responder::class);

        $successResponse = $responder->success()->respond();
        $errorResponse = $responder->error()->respond();

        $this->assertInstanceOf(JsonResponse::class, $successResponse);
        $this->assertInstanceOf(JsonResponse::class, $errorResponse);
    }

    /**
     * Assert that you can create responses using the responder helper function.
     */
    public function testCreateResponsesWithHelper()
    {
        $successResponse = responder()->success()->respond();
        $errorResponse = responder()->error()->respond();

        $this->assertInstanceOf(JsonResponse::class, $successResponse);
        $this->assertInstanceOf(JsonResponse::class, $errorResponse);
    }

    /**
     * Assert that you can create responses using the responder helper function.
     */
    public function testCreateResponsesWithFacade()
    {
        $successResponse = ResponderFacade::success()->respond();
        $errorResponse = ResponderFacade::error()->respond();

        $this->assertInstanceOf(JsonResponse::class, $successResponse);
        $this->assertInstanceOf(JsonResponse::class, $errorResponse);
    }

    /**
     * Assert that you can create responses using the responder helper function.
     */
    public function testCreateResponsesWithTrait()
    {
        $trait = $this->getObjectForTrait(MakesResponses::class);

        $successResponse = $trait->success()->respond();
        $errorResponse = $trait->error()->respond();

        $this->assertInstanceOf(JsonResponse::class, $successResponse);
        $this->assertInstanceOf(JsonResponse::class, $errorResponse);
    }

    /**
     * Assert that you can set the status code for the response.
     */
    public function testSetStatusCode()
    {
        $successResponse = responder()->success()->respond(201);
        $errorResponse = responder()->error()->respond(404);

        $this->assertEquals(201, $successResponse->status());
        $this->assertEquals(404, $errorResponse->status());
    }

    /**
     * Assert that you can set headers for the response.
     */
    public function testSetHeaders()
    {
        $successResponse = responder()->success()->respond(null, ['x-foo' => true]);
        $errorResponse = responder()->error()->respond(null, ['x-foo' => false]);

        $this->assertEquals(true, $successResponse->headers->get('x-foo'));
        $this->assertEquals(false, $errorResponse->headers->get('x-foo'));
    }

    /**
     * Assert that you can cast the response data to an array.
     */
    public function testCastToArray()
    {
        $success = responder()->success()->toArray();
        $error = responder()->error()->toArray();

        $this->assertIsArray($success);
        $this->assertIsArray($error);
    }

    /**
     * Assert that you can cast the response data to a collection.
     */
    public function testCastToCollection()
    {
        $success = responder()->success()->toCollection();
        $error = responder()->error()->toCollection();

        $this->assertInstanceOf(Collection::class, $success);
        $this->assertInstanceOf(Collection::class, $error);
    }

    /**
     * Assert that you can cast the response data to a JSON string.
     */
    public function testCastToJson()
    {
        $success = responder()->success()->toJson();
        $error = responder()->error()->toJson();

        $this->assertIsString($success);
        $this->assertIsString($error);
    }
}