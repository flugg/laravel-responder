<?php

namespace Flugg\Responder\Tests\Unit\Exceptions;

use Exception;
use Flugg\Responder\Adapters\IlluminateValidatorAdapter;
use Flugg\Responder\Contracts\Responder;
use Flugg\Responder\Exceptions\Handler;
use Flugg\Responder\Http\Builders\ErrorResponseBuilder;
use Flugg\Responder\Tests\UnitTestCase;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use LogicException;
use Mockery;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Unit tests for the [Flugg\Responder\Exceptions\Handler] class.
 *
 * @see \Flugg\Responder\Exceptions\Handler
 */
class HandlerTest extends UnitTestCase
{
    /**
     * Mock of an exception handler.
     *
     * @var \Mockery\MockInterface|\Illuminate\Contracts\Debug\ExceptionHandler
     */
    protected $exceptionHandler;

    /**
     * Mock of a responder service.
     *
     * @var \Mockery\MockInterface|\Flugg\Responder\Contracts\Responder
     */
    protected $responder;

    /**
     * Mock of a request object.
     *
     * @var \Mockery\MockInterface|\Illuminate\Http\Request
     */
    protected $request;

    /**
     * Exception handler decorator.
     *
     * @var \Mockery\MockInterface|\Flugg\Responder\Exceptions\Handler
     */
    protected $handler;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->exceptionHandler = mock(ExceptionHandler::class);
        $this->config = mock(Repository::class);
        $this->responder = mock(Responder::class);
        $this->request = mock(Request::class);
        $this->handler = new Handler($this->exceptionHandler, $this->config, $this->responder);
    }

    /**
     * Assert that [render] returns an error response when given a configured exception.
     */
    public function testRenderMethodConvertsException()
    {
        $this->request->allows('expectsJson')->andReturn(true);
        $this->responder->allows('error')->andReturn($responseBuilder = mock(ErrorResponseBuilder::class));
        $responseBuilder->allows('respond')->andReturn($response = mock(JsonResponse::class));
        $this->config->allows('get')->with('responder.exceptions')->andReturn([
            LogicException::class => ['code' => 'server_error', 'status' => 500]
        ]);
        $this->config->allows('get')->with('app.debug')->andReturn(false);

        $result = $this->handler->render($this->request, $exception = new LogicException());

        $this->assertSame($response, $result);
        $this->responder->shouldHaveReceived('error')->with($exception);
        $responseBuilder->shouldHaveReceived('respond');
    }

    /**
     * Assert that [render] returns an error response when given a child class of a configured exception.
     */
    public function testRenderMethodConvertsChildExceptions()
    {
        $this->request->allows('expectsJson')->andReturn(true);
        $this->responder->allows('error')->andReturn($responseBuilder = mock(ErrorResponseBuilder::class));
        $responseBuilder->allows('respond')->andReturn($response = mock(JsonResponse::class));
        $this->config->allows('get')->with('responder.exceptions')->andReturn([
            Exception::class => ['code' => 'error', 'status' => 500],
        ]);
        $this->config->allows('get')->with('app.debug')->andReturn(false);

        $result = $this->handler->render($this->request, $exception = new FileException());

        $this->assertSame($response, $result);
        $this->responder->shouldHaveReceived('error')->with($exception);
        $responseBuilder->shouldHaveReceived('respond');
    }

    /**
     * Assert that [render] returns a validation error response when given a configured validation exception.
     */
    public function testRenderMethodConvertsValidationExceptionsWithValidator()
    {
        $this->request->allows('expectsJson')->andReturn(true);
        $this->responder->allows('error')->andReturn($responseBuilder = mock(ErrorResponseBuilder::class));
        $responseBuilder->allows('validator')->andReturnSelf();
        $responseBuilder->allows('respond')->andReturn($response = mock(JsonResponse::class));
        $this->config->allows('get')->with('responder.exceptions')->andReturn([
            ValidationException::class => ['code' => 'validation_error', 'status' => 422],
        ]);
        $this->config->allows('get')->with('app.debug')->andReturn(false);

        $result = $this->handler->render($this->request, $exception = new ValidationException(mock(Validator::class)));

        $this->assertSame($response, $result);
        $this->responder->shouldHaveReceived('error')->with($exception);
        $responseBuilder->shouldHaveReceived('validator')->with(Mockery::on(function ($argument) {
            return $argument instanceof IlluminateValidatorAdapter;
        }));
        $responseBuilder->shouldHaveReceived('respond');
    }

    /**
     * Assert that [render] returns an error response when given a configured HTTP exception in debug mode.
     */
    public function testRenderMethodConvertsHttpExceptionsInDebug()
    {
        $this->request->allows('expectsJson')->andReturn(true);
        $this->responder->allows('error')->andReturn($responseBuilder = mock(ErrorResponseBuilder::class));
        $responseBuilder->allows('respond')->andReturn($response = mock(JsonResponse::class));
        $this->config->allows('get')->with('responder.exceptions')->andReturn([
            BadRequestHttpException::class => ['code' => 'error', 'status' => 400],
        ]);
        $this->config->allows('get')->with('app.debug')->andReturn(true);

        $result = $this->handler->render($this->request, $exception = new BadRequestHttpException());

        $this->assertSame($response, $result);
        $this->responder->shouldHaveReceived('error')->with($exception);
        $responseBuilder->shouldHaveReceived('respond');
    }

    /**
     * Assert that [render] forwards the exception when given a configured server exception in debug mode.
     */
    public function testRenderMethodForwardsServerExceptionsInDebug()
    {
        $this->request->allows('expectsJson')->andReturn(true);
        $this->exceptionHandler->allows('render')->andReturn($response = mock(JsonResponse::class));
        $this->config->allows('get')->with('responder.exceptions')->andReturn([
            LogicException::class => ['code' => 'error', 'status' => 500],
        ]);
        $this->config->allows('get')->with('app.debug')->andReturn(true);

        $result = $this->handler->render($this->request, $exception = new LogicException());

        $this->assertSame($response, $result);
        $this->exceptionHandler->shouldHaveReceived('render')->with($this->request, $exception);
    }

    /**
     * Assert that [render] forwards the exception when it's not a JSON request.
     */
    public function testRenderMethodForwardsExceptionsWhenNotJson()
    {
        $this->request->allows('expectsJson')->andReturn(false);
        $this->exceptionHandler->allows('render')->andReturn($response = mock(JsonResponse::class));

        $result = $this->handler->render($this->request, $exception = new LogicException());

        $this->assertSame($response, $result);
        $this->exceptionHandler->shouldHaveReceived('render')->with($this->request, $exception);
    }

    /**
     * Assert that [render] forwards the exception when given an unconfigured exception.
     */
    public function testRenderMethodForwardsUnconfiguredExceptions()
    {
        $this->request->allows('expectsJson')->andReturn(true);
        $this->exceptionHandler->allows('render')->andReturn($response = mock(JsonResponse::class));
        $this->config->allows('get')->with('responder.exceptions')->andReturn([]);

        $result = $this->handler->render($this->request, $exception = new FileException());

        $this->assertSame($response, $result);
        $this->exceptionHandler->shouldHaveReceived('render')->with($this->request, $exception);
    }

    /**
     * Assert that [report] forwards the method call to the original handler.
     */
    public function testReportMethodIsForwardedToHandler()
    {
        $this->exceptionHandler->allows('report');

        $this->handler->report($exception = new LogicException());

        $this->exceptionHandler->shouldHaveReceived('report')->with($exception);
    }

    /**
     * Assert that [shouldReport] forwards the method call to the original handler.
     */
    public function testShouldReportMethodIsForwardedToHandler()
    {
        $this->exceptionHandler->allows('shouldReport')->andReturn($shouldReport = true);

        $result = $this->handler->shouldReport($exception = new LogicException());

        $this->assertSame($shouldReport, $result);
        $this->exceptionHandler->shouldHaveReceived('shouldReport')->with($exception);
    }

    /**
     * Assert that [renderForConsole] forwards the method call to the original handler.
     */
    public function testRenderForConsoleMethodIsForwardedToHandler()
    {
        $this->exceptionHandler->allows('renderForConsole')->andReturn($response = mock(JsonResponse::class));

        $this->handler->renderForConsole($output = mock(OutputInterface::class), $exception = new LogicException());

        $this->exceptionHandler->shouldHaveReceived('renderForConsole')->with($output, $exception);
    }

    /**
     * Assert that it forwards all other method calls to the original handler.
     */
    public function testOtherMethodsAreForwardedToHandler()
    {
        $this->exceptionHandler->allows('foo')->andReturn($bar = 123);

        $result = $this->handler->foo($baz = 456);

        $this->assertSame($bar, $result);
        $this->exceptionHandler->shouldHaveReceived('foo')->with($baz);
    }
}
