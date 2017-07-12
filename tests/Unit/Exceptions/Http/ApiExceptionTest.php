<?php

namespace Flugg\Responder\Tests\Unit;

use Exception;
use Flugg\Responder\Exceptions\Handler;
use Flugg\Responder\Exceptions\Http\ApiException;
use Flugg\Responder\Exceptions\Http\RelationNotFoundException;
use Flugg\Responder\Exceptions\Http\ResourceNotFoundException;
use Flugg\Responder\Exceptions\Http\UnauthenticatedException;
use Flugg\Responder\Exceptions\Http\UnauthorizedException;
use Flugg\Responder\Exceptions\Http\ValidationFailedException;
use Flugg\Responder\Http\Responses\ErrorResponseBuilder;
use Flugg\Responder\Tests\TestCase;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\RelationNotFoundException as BaseRelationNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Mockery;

/**
 * Unit tests for the [Flugg\Responder\Exceptions\Handler] class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class ApiExceptionTest extends TestCase
{
    /**
     * The exception handler.
     *
     * @var \Flugg\Responder\Exceptions\Http\ApiException
     */
    protected $exception;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->exception = new class extends ApiException
        {
            protected $status = 404;
            protected $errorCode = 'test_error';
            protected $message = 'An error has occured.';
            protected $data = ['foo' => 1];
        };
    }

    /**
     *
     */
    public function testStatusCodeMethodReturnsSetStatusCode()
    {
        $status = $this->exception->statusCode();

        $this->assertEquals(404, $status);
    }

    /**
     *
     */
    public function testErrorCodeMethodReturnsSetErrorCode()
    {
        $errorCode = $this->exception->errorCode();

        $this->assertEquals('test_error', $errorCode);
    }

    /**
     *
     */
    public function testMessageMethodReturnsSetMessage()
    {
        $message = $this->exception->message();

        $this->assertEquals('An error has occured.', $message);
    }

    /**
     *
     */
    public function testDataMethodReturnsSetErrorData()
    {
        $data = $this->exception->data();

        $this->assertEquals(['foo' => 1], $data);
    }
}