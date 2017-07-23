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
class HandlerTest extends TestCase
{
    /**
     * A mock of a request object class.
     *
     * @var \Mockery\MockInterface
     */
    protected $request;

    /**
     * A mock of Laravel's container contract.
     *
     * @var \Mockery\MockInterface
     */
    protected $container;

    /**
     * The exception handler being tested.
     *
     * @var \Flugg\Responder\Exceptions\Handler;
     */
    protected $handler;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->container = Mockery::mock(Container::class);
        $this->request = Mockery::mock(Request::class);
        $this->handler = new Handler($this->container);
    }

    /**
     *
     */
    public function testRenderMethodTransformsUnauthenticationExceptions()
    {
        $exception = new AuthenticationException;
        $this->expectException(UnauthenticatedException::class);

        $this->handler->render($this->request, $exception);
    }

    /**
     *
     */
    public function testRenderMethodTransformsUnauthorizedExceptions()
    {
        $exception = new AuthorizationException;
        $this->expectException(UnauthorizedException::class);

        $this->handler->render($this->request, $exception);
    }

    /**
     *
     */
    public function testRenderMethodTransformsModelNotFoundExceptions()
    {
        $exception = new ModelNotFoundException;
        $this->expectException(ResourceNotFoundException::class);

        $this->handler->render($this->request, $exception);
    }

    /**
     *
     */
    public function testRenderMethodTransformsRelationNotFoundExceptions()
    {
        $exception = new BaseRelationNotFoundException;
        $this->expectException(RelationNotFoundException::class);

        $this->handler->render($this->request, $exception);
    }

    /**
     *
     */
    public function testRenderMethodTransformsValidationExceptions()
    {
        $exception = new ValidationException($validator = Mockery::mock(Validator::class));
        $this->expectException(ValidationFailedException::class);

        $this->handler->render($this->request, $exception);
    }

    /**
     *
     */
    public function testRenderMethodConvertsExceptionToJsonResponse()
    {
        $exception = Mockery::mock(ApiException::class);
        $exception->shouldReceive('errorCode')->andReturn($errorCode = 'test_error');
        $exception->shouldReceive('message')->andReturn($message = 'A test error has occured.');
        $exception->shouldReceive('data')->andReturn($data = ['foo' => 1]);
        $exception->shouldReceive('statusCode')->andReturn($status = 404);
        $this->container->shouldReceive('make')->andReturn($responseBuilder = $this->mockErrorResponseBuilder());
        $responseBuilder->shouldReceive('respond')->andReturn($response = new JsonResponse);

        $result = $this->handler->render($this->request, $exception);

        $this->assertSame($response, $result);
        $this->container->shouldHaveReceived('make')->with(ErrorResponseBuilder::class)->once();
        $responseBuilder->shouldHaveReceived('error')->with($errorCode, $message)->once();
        $responseBuilder->shouldHaveReceived('data')->with($data)->once();
        $responseBuilder->shouldHaveReceived('respond')->with($status)->once();
    }

    /**
     *
     */
    public function testItShouldNotTouchNonApiExceptions()
    {
        $exception = new Exception;

        $result = $this->handler->render($this->request, $exception);

        $this->assertInstanceOf(Response::class, $result);
    }
}