<?php

namespace Flugg\Responder\Tests\Unit;

use Exception;
use Flugg\Responder\Contracts\Responder;
use Flugg\Responder\Exceptions\Handler;
use Flugg\Responder\Exceptions\Http\HttpException;
use Flugg\Responder\Exceptions\Http\PageNotFoundException;
use Flugg\Responder\Exceptions\Http\RelationNotFoundException;
use Flugg\Responder\Exceptions\Http\UnauthenticatedException;
use Flugg\Responder\Exceptions\Http\UnauthorizedException;
use Flugg\Responder\Exceptions\Http\ValidationFailedException;
use Flugg\Responder\Tests\TestCase;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Translation\Translator;
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
final class HandlerTest extends TestCase
{
    /**
     * A mock of a [Request] object.
     *
     * @var \Mockery\MockInterface
     */
    protected $request;

    /**
     * A mock of a [Container] class.
     *
     * @var \Mockery\MockInterface
     */
    protected $container;

    /**
     * The [Handler] class being tested.
     *
     * @var \Flugg\Responder\Exceptions\Handler;
     */
    protected $handler;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->request = Mockery::mock(Request::class);
        $this->request->shouldReceive('wantsJson')->andReturn(true);

        $this->container = Mockery::mock(Container::class);
        $this->handler = new Handler($this->container);
        $this->app->instance(Container::class, $this->container);
    }

    /**
     * Assert that the [render] method converts [AuthenticationException] exceptions to
     * the package's [UnauthenticatedException].
     */
    public function testRenderMethodConvertsUnauthenticationExceptions(): void
    {
        $exception = new AuthenticationException();
        $this->expectException(UnauthenticatedException::class);

        $this->handler->render($this->request, $exception);
    }

    /**
     * Assert that the [render] method converts [AuthorizationException] exceptions to
     * the package's [UnauthorizedException].
     */
    public function testRenderMethodConvertsUnauthorizedExceptions(): void
    {
        $exception = new AuthorizationException();
        $this->expectException(UnauthorizedException::class);

        $this->handler->render($this->request, $exception);
    }

    /**
     * Assert that the [render] method converts [ModelNotFoundException] exceptions to
     * the package's [PageNotFoundException].
     */
    public function testRenderMethodConvertsModelNotFoundExceptions(): void
    {
        $exception = new ModelNotFoundException();
        $this->expectException(PageNotFoundException::class);

        $this->handler->render($this->request, $exception);
    }

    /**
     * Assert that the [render] method converts [RelationNotFoundException] exceptions to
     * the package's [RelationNotFoundException].
     */
    public function testRenderMethodConvertsRelationNotFoundExceptions(): void
    {
        $exception = new BaseRelationNotFoundException();
        $this->expectException(RelationNotFoundException::class);

        $this->handler->render($this->request, $exception);
    }

    /**
     * Assert that the [render] method converts [ValidationException] exceptions to
     * the package's [ValidationFailedException].
     */
    public function testRenderMethodConvertsValidationExceptions(): void
    {
        $validator = Mockery::mock(Validator::class);
        $validator->shouldReceive('errors')->andReturn(collect([['foo' => 'bar']]));
        $translator = Mockery::mock(Translator::class);
        $translator->shouldReceive('get')->andReturn('foo');
        $validator->shouldReceive('getTranslator')->andReturn($translator);
        $exception = new ValidationException($validator);
        $this->expectException(ValidationFailedException::class);

        $this->handler->render($this->request, $exception);
    }

    /**
     * Assert that the [render] method converts [HttpException] exceptions to responses.
     */
    public function testRenderMethodConvertsHttpExceptionsToResponses(): void
    {
        $exception = Mockery::mock(HttpException::class);
        $exception->shouldReceive('errorCode')->andReturn($errorCode = 'test_error');
        $exception->shouldReceive('message')->andReturn($message = 'A test error has occured.');
        $exception->shouldReceive('data')->andReturn($data = ['foo' => 1]);
        $exception->shouldReceive('statusCode')->andReturn($status = 404);
        $exception->shouldReceive('getHeaders')->andReturn($headers = ['x-foo' => 123]);
        $this->app->instance(Responder::class, $responder = Mockery::mock(Responder::class));
        $responder->shouldReceive('error')->andReturn($responseBuilder = $this->mockErrorResponseBuilder());
        $responseBuilder->shouldReceive('respond')->andReturn($response = new JsonResponse());

        $result = $this->handler->render($this->request, $exception);

        $this->assertSame($response, $result);
        $responder->shouldHaveReceived('error')->with($errorCode, $message)->once();
        $responseBuilder->shouldHaveReceived('data')->with($data)->once();
        $responseBuilder->shouldHaveReceived('respond')->with($status, $headers)->once();
    }

    /**
     * Assert that the [render] method leaves other exceptions untouched.
     */
    public function testItShouldNotConvertNonHttpExceptions(): void
    {
        $request = Request::createFromGlobals();

        $result = $this->handler->render($request, $exception = new Exception());

        $this->assertInstanceOf(Response::class, $result);
    }
}
