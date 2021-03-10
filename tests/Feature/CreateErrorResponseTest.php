<?php

namespace Flugg\Responder\Tests\Feature;

use Flugg\Responder\Tests\FeatureTestCase;
use Illuminate\Support\Facades\Route;
use InvalidArgumentException;

/**
 * Feature tests asserting that you can create error responses.
 */
class CreateErrorResponseTest extends FeatureTestCase
{
    /**
     * Assert that you can create error responses without [respond].
     */
    public function testCreateErrorResponseWithoutRespondMethod()
    {
        Route::get('/', function () {
            return responder()->error();
        });

        $response = $this->json('get', '/');

        $response->assertStatus(500)->assertExactJson(['error' => ['code' => null]]);
    }

    /**
     * Assert that you can create error responses with [respond].
     */
    public function testCreateErrorResponseWithRespondMethod()
    {
        Route::get('/', function () {
            return responder()->error()->respond();
        });

        $response = $this->json('get', '/');

        $response->assertStatus(500)->assertExactJson(['error' => ['code' => null]]);
    }

    /**
     * Assert that you can create error responses with status code and headers.
     */
    public function testCreateErrorResponseWithStatusCodeAndHeaders()
    {
        Route::get('/', function () {
            return responder()->error()->respond(501, ['x-foo' => 1]);
        });

        $response = $this->json('get', '/');

        $response->assertStatus(501)->assertHeader('x-foo', 1)->assertExactJson(['error' => ['code' => null]]);
    }

    /**
     * Assert that you can create error responses with error code and message.
     */
    public function testCreateErrorResponseWithCodeAndMessage()
    {
        $code = 'foo';
        $message = 'bar';
        Route::get('/', function () use ($code, $message) {
            return responder()->error($code, $message)->respond();
        });

        $response = $this->json('get', '/');

        $response->assertStatus(500)->assertExactJson(['error' => [
            'code' => $code,
            'message' => $message,
        ]]);
    }

    /**
     * Assert that you can create error responses with error code where message is resolved from config.
     */
    public function testCreateErrorResponseWithCodeAndConfiguredMessage()
    {
        config(['responder.error_messages' => [($code = 'foo') => $message = 'bar']]);
        Route::get('/', function () use ($code) {
            return responder()->error($code)->respond();
        });

        $response = $this->json('get', '/');

        $response->assertStatus(500)->assertExactJson(['error' => [
            'code' => $code,
            'message' => $message,
        ]]);
    }

    /**
     * Assert that you can create error responses with exception where message is resolved from exception.
     */
    public function testCreateErrorResponseWithException()
    {
        $exception = new InvalidArgumentException($message = 'foo');
        Route::get('/', function () use ($exception) {
            return responder()->error($exception)->respond();
        });

        $response = $this->json('get', '/');

        $response->assertStatus(500)->assertExactJson(['error' => [
            'code' => 'invalid_argument',
            'message' => $message,
        ]]);
    }

    /**
     * Assert that you can create error responses with exception where error code and status code is resolved from config.
     */
    public function testCreateErrorResponseWithExceptionUsingConfiguredCodeAndStatus()
    {
        $exception = new InvalidArgumentException($message = 'foo');
        config(['responder.exceptions' => [get_class($exception) => [
            'code' => $code = 'foo',
            'status' => $status = 501,
        ]]]);
        Route::get('/', function () use ($exception) {
            return responder()->error($exception)->respond();
        });

        $response = $this->json('get', '/');

        $response->assertStatus($status)->assertExactJson(['error' => [
            'code' => $code,
            'message' => $message,
        ]]);
    }

    /**
     * Assert that you can create error responses with exception where message is resolved from config.
     */
    public function testCreateErrorResponseWithExceptionUsingConfiguredMessage()
    {
        config(['responder.error_messages' => [($code = 'invalid_argument') => $message = 'foo']]);
        $exception = new InvalidArgumentException;
        Route::get('/', function () use ($exception) {
            return responder()->error($exception)->respond();
        });

        $response = $this->json('get', '/');

        $response->assertStatus(500)->assertExactJson(['error' => [
            'code' => $code,
            'message' => $message,
        ]]);
    }

    /**
     * Assert that you can create error responses with exception where error code, status code and message is resolved from config.
     */
    public function testCreateErrorResponseWithExceptionUsingConfiguredCodeAndStatusAndMessage()
    {
        $exception = new InvalidArgumentException;
        config(['responder.exceptions' => [get_class($exception) => [
            'code' => $code = 'foo',
            'status' => $status = 501,
        ]]]);
        config(['responder.error_messages' => [$code => $message = 'bar']]);
        Route::get('/', function () use ($exception) {
            return responder()->error($exception)->respond();
        });

        $response = $this->json('get', '/');

        $response->assertStatus($status)->assertExactJson(['error' => [
            'code' => $code,
            'message' => $message,
        ]]);
    }

    /**
     * Assert that you can create error responses with eror code and exception where message is resolved from exception.
     */
    public function testCreateErrorResponseWithCodeAndException()
    {
        $code = 'foo';
        $exception = new InvalidArgumentException($message = 'bar');
        Route::get('/', function () use ($code, $exception) {
            return responder()->error($code, $exception)->respond();
        });

        $response = $this->json('get', '/');

        $response->assertStatus(500)->assertExactJson(['error' => [
            'code' => $code,
            'message' => $message,
        ]]);
    }

    /**
     * Assert that you can create error responses with error code and exception where message is resolved from config.
     */
    public function testCreateErrorResponseWithCodeAndExceptionUsingConfiguredMessage()
    {
        config(['responder.error_messages' => [($code = 'foo') => $message = 'bar']]);
        $exception = new InvalidArgumentException;
        Route::get('/', function () use ($code, $exception) {
            return responder()->error($code, $exception)->respond();
        });

        $response = $this->json('get', '/');

        $response->assertStatus(500)->assertExactJson(['error' => [
            'code' => $code,
            'message' => $message,
        ]]);
    }

    /**
     * Assert that you can create error responses with error code and exception where message and status code is resolved from config.
     */
    public function testCreateErrorResponseWithCodeAndExceptionUsingConfiguredStatusAndMessage()
    {
        $exception = new InvalidArgumentException;
        config(['responder.exceptions' => [get_class($exception) => [
            'status' => $status = 501,
        ]]]);
        config(['responder.error_messages' => [($code = 'bar') => $message = 'baz']]);
        Route::get('/', function () use ($code, $exception) {
            return responder()->error($code, $exception)->respond();
        });

        $response = $this->json('get', '/');

        $response->assertStatus($status)->assertExactJson(['error' => [
            'code' => $code,
            'message' => $message,
        ]]);
    }
}
