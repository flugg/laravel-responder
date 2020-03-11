<?php

namespace Flugg\Responder\Tests\Unit\Exceptions;

use Exception;
use Flugg\Responder\Responder;
use Flugg\Responder\Exceptions\Handler;
use Flugg\Responder\Http\Builders\ErrorResponseBuilder;
use Flugg\Responder\Tests\IntegrationTestCase;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use LogicException;
use Mockery\MockInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Unit tests for the [Flugg\Responder\Exceptions\Handler] class.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class HandlerTest extends IntegrationTestCase
{
    /**
     * Mock of an exception handler.
     *
     * @var MockInterface|ExceptionHandler
     */
    protected $exceptionHandler;

    /**
     * Mock of a responder service.
     *
     * @var MockInterface|Responder
     */
    protected $responder;

    /**
     * Mock of a request object.
     *
     * @var MockInterface|Request
     */
    protected $request;

    /**
     * Exception handler decorator.
     *
     * @var MockInterface|Handler
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
        $this->responder = mock(Responder::class);
        $this->request = mock(Request::class);
        $this->handler = new Handler($this->exceptionHandler, $this->responder);

        config()->set('responder.exceptions', [
            BadRequestHttpException::class => ['code' => 'client_error', 'status' => 400],
            LogicException::class => ['code' => 'server_error', 'status' => 500]
        ]);
    }

    /**
     * Assert that [render] returns an error response when given a configured exception.
     */
    public function testRenderMethodConvertsException()
    {
        $this->request->allows('expectsJson')->andReturn(true);
        $this->responder->allows('error')->andReturn($responseBuilder = mock(ErrorResponseBuilder::class));
        $responseBuilder->allows('respond')->andReturn($response = mock(JsonResponse::class));

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
        config()->set('responder.exceptions', [
            Exception::class => ['code' => 'error', 'status' => 500],
        ]);

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
        config()->set('responder.exceptions', [
            ValidationException::class => ['code' => 'validation_error', 'status' => 422],
        ]);

        $result = $this->handler->render($this->request, $exception = new ValidationException($validator = mock(Validator::class)));

        $this->assertSame($response, $result);
        $this->responder->shouldHaveReceived('error')->with($exception);
        $responseBuilder->shouldHaveReceived('validator')->with($validator);
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
        config()->set('app.debug', true);

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
        config()->set('app.debug', true);

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

        $result = $this->handler->renderForConsole($output = mock(OutputInterface::class), $exception = new LogicException());

        $this->assertSame($response, $result);
        $this->exceptionHandler->shouldHaveReceived('renderForConsole')->with($output, $exception);
    }
}
