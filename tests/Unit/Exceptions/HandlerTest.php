<?php

namespace Flugg\Responder\Tests\Unit\Exceptions;

use Exception;
use Flugg\Responder\Adapters\IlluminateValidatorAdapter;
use Flugg\Responder\Contracts\Responder;
use Flugg\Responder\Exceptions\Handler;
use Flugg\Responder\Tests\UnitTestCase;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use LogicException;
use Prophecy\Argument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

/**
 * Unit tests for the [Handler] class.
 *
 * @see \Flugg\Responder\Exceptions\Handler
 */
class HandlerTest extends UnitTestCase
{
    /**
     * Mock of an [\Illuminate\Contracts\Debug\ExceptionHandler] interface.
     *
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    protected $exceptionHandler;

    /**
     * Mock of a [\Illuminate\Contracts\Config\Repository] interface.
     *
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    protected $config;

    /**
     * Mock of a [\Flugg\Responder\Contracts\Responder] interface.
     *
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    protected $responder;

    /**
     * Class being tested.
     *
     * @var \Flugg\Responder\Exceptions\Handler
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

        $this->exceptionHandler = $this->prophesize(ExceptionHandler::class);
        $this->config = $this->prophesize(Repository::class);
        $this->responder = $this->prophesize(Responder::class);
        $this->handler = new Handler($this->exceptionHandler->reveal(), $this->config->reveal(), $this->responder->reveal());
    }

    /**
     * Assert that [render] returns an error response when given a configured exception.
     */
    public function testRenderMethodConvertsException()
    {
        $request = $this->mockRequest();
        $exception = new LogicException();
        $responseBuilder = $this->mockErrorResponseBuilder($response = new JsonResponse());
        $this->responder->error($exception)->willReturn($responseBuilder);
        $this->config->get('responder.exceptions')->willReturn([LogicException::class => []]);
        $this->config->get('app.debug')->willReturn(false);

        $result = $this->handler->render($request->reveal(), $exception);

        $this->assertSame($response, $result);
    }

    /**
     * Assert that [render] returns an error response when given a child class of a configured exception.
     */
    public function testRenderMethodConvertsChildExceptions()
    {
        $request = $this->mockRequest();
        $exception = new FileException();
        $responseBuilder = $this->mockErrorResponseBuilder($response = new JsonResponse());
        $this->responder->error($exception)->willReturn($responseBuilder);
        $this->config->get('responder.exceptions')->willReturn([Exception::class => []]);
        $this->config->get('app.debug')->willReturn(false);

        $result = $this->handler->render($request->reveal(), $exception);

        $this->assertSame($response, $result);
    }

    /**
     * Assert that [render] returns a validation error response when given a configured [ValidationException].
     */
    public function testRenderMethodConvertsValidationExceptionsWithValidator()
    {
        $request = $this->mockRequest();
        $validator = $this->prophesize(Validator::class);
        $exception = new ValidationException($validator->reveal());
        $responseBuilder = $this->mockErrorResponseBuilder($response = new JsonResponse());
        $responseBuilder->validator(Argument::cetera())->willReturn($responseBuilder);
        $this->responder->error($exception)->willReturn($responseBuilder);
        $this->config->get('responder.exceptions')->willReturn([ValidationException::class => []]);
        $this->config->get('app.debug')->willReturn(false);

        $result = $this->handler->render($request->reveal(), $exception);

        $this->assertSame($response, $result);
        $responseBuilder->validator(Argument::type(IlluminateValidatorAdapter::class))->shouldHaveBeenCalledOnce();
    }

    /**
     * Assert that [render] converts exceptions to an error response when status code is 4xx and in debug mode.
     */
    public function testRenderMethodConvertsExceptionsWith4xxStatusCodesInDebugMode()
    {
        $request = $this->mockRequest();
        $exception = new LogicException();
        $responseBuilder = $this->mockErrorResponseBuilder($response = new JsonResponse());
        $this->responder->error($exception)->willReturn($responseBuilder);
        $this->config->get('responder.exceptions')->willReturn([LogicException::class => ['status' => 400]]);
        $this->config->get('app.debug')->willReturn(true);

        $result = $this->handler->render($request->reveal(), $exception);

        $this->assertSame($response, $result);
    }

    /**
     * Assert that [render] forwards the exception to the base handler when status code is 5xx and in debug mode.
     */
    public function testRenderMethodIsForwardedWith5xxStatusCodeInDebugMode()
    {
        $request = $this->mockRequest();
        $exception = new LogicException();
        $this->exceptionHandler->render($request, $exception)->willReturn($response = new JsonResponse());
        $this->config->get('responder.exceptions')->willReturn([LogicException::class => ['status' => 500]]);
        $this->config->get('app.debug')->willReturn(true);

        $result = $this->handler->render($request, $exception);

        $this->assertSame($response, $result);
    }

    /**
     * Assert that [render] forwards the exception to the base handler when it's not a JSON request.
     */
    public function testRenderMethodIsForwardedWhenNotJson()
    {
        $request = $this->mockRequest(false);
        $exception = new LogicException();
        $this->exceptionHandler->render($request, $exception)->willReturn($response = new JsonResponse());
        $this->config->get('responder.exceptions')->willReturn([LogicException::class => []]);
        $this->config->get('app.debug')->willReturn(false);

        $result = $this->handler->render($request->reveal(), $exception);

        $this->assertSame($response, $result);
    }

    /**
     * Assert that [render] forwards the call to the base handler when given a non configured exception.
     */
    public function testRenderMethodIsForwardedWithNonConfiguredExceptions()
    {
        $request = $this->mockRequest();
        $exception = new LogicException();
        $this->exceptionHandler->render($request, $exception)->willReturn($response = new JsonResponse());
        $this->config->get('responder.exceptions')->willReturn([]);
        $this->config->get('app.debug')->willReturn(false);

        $result = $this->handler->render($request->reveal(), $exception);

        $this->assertSame($response, $result);
    }

    /**
     * Assert that [report] forwards the call to the base handler.
     */
    public function testReportMethodIsForwarded()
    {
        $this->handler->report($exception = new LogicException());

        $this->exceptionHandler->report($exception)->shouldHaveBeenCalledOnce();
    }

    /**
     * Assert that [report] forwards the call to the base handler.
     */
    public function testShouldReportMethodIsForwarded()
    {
        $exception = new LogicException();
        $this->exceptionHandler->shouldReport($exception)->willReturn($shouldReport = true);

        $result = $this->handler->shouldReport($exception);

        $this->assertSame($shouldReport, $result);
        $this->exceptionHandler->shouldReport($exception)->shouldHaveBeenCalledOnce();
    }

    /**
     * Assert that [renderForConsole] forwards the call to the base handler.
     */
    public function testRenderForConsoleMethodIsForwarded()
    {
        $output = $this->prophesize(OutputInterface::class);
        $exception = new LogicException();

        $this->handler->renderForConsole($output->reveal(), $exception);

        $this->exceptionHandler->renderForConsole($output, $exception)->shouldHaveBeenCalledOnce();
    }

    /**
     * Assert that the handler forwards all other method calls to the base handler.
     */
    public function testOtherMethodsAreForwarded()
    {
        $exceptionHandler = $this->prophesize(ClassWithFooMethod::class);
        $exceptionHandler->willImplement(ExceptionHandler::class);
        $exceptionHandler->foo($foo = 1)->willReturn($bar = 2);
        $this->handler = new Handler($exceptionHandler->reveal(), $this->config->reveal(), $this->responder->reveal());

        $result = $this->handler->foo($foo);

        $this->assertSame($bar, $result);
        $exceptionHandler->foo($foo)->shouldHaveBeenCalledOnce();
    }
}

/** Abstract stub class with a [foo] method. */
abstract class ClassWithFooMethod
{
    abstract public function foo($foo);
}
