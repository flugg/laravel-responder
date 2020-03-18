<?php

namespace Flugg\Responder\Exceptions;

use Flugg\Responder\Contracts\Responder;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Exception handler decorating an existing handler and adds conversion logic.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class Handler implements ExceptionHandler
{
    /**
     * Decorated exception handler.
     *
     * @var ExceptionHandler
     */
    protected $handler;

    /**
     * Responder service for making error responses.
     *
     * @var Responder
     */
    protected $responder;

    /**
     * Create a new exception handler instance.
     *
     * @param ExceptionHandler $handler
     * @param Responder $responder
     */
    public function __construct(ExceptionHandler $handler, Responder $responder)
    {
        $this->handler = $handler;
        $this->responder = $responder;
    }

    /**
     * Report or log an exception.
     *
     * @param Throwable $exception
     * @return void
     * @throws Exception
     */
    public function report(Throwable $exception)
    {
        return $this->handler->report($exception);
    }

    /**
     * Determine if the exception should be reported.
     *
     * @param Throwable $exception
     * @return bool
     */
    public function shouldReport(Throwable $exception)
    {
        return $this->handler->shouldReport($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param Request $request
     * @param Throwable $exception
     * @return Response
     * @throws Throwable
     */
    public function render($request, Throwable $exception)
    {
        if ($request->expectsJson() && $this->shouldConvertException($exception)) {
            return $this->convertException($exception);
        }

        return $this->handler->render($request, $exception);
    }

    /**
     * Render an exception to the console.
     *
     * @param OutputInterface $output
     * @param Throwable $exception
     * @return void
     */
    public function renderForConsole($output, Throwable $exception)
    {
        return $this->handler->renderForConsole($output, $exception);
    }

    /**
     * Forward method calls to the original exception handler.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->handler->{$method}(...$parameters);
    }

    /**
     * Check if the exception should be converted to an error response.
     *
     * @param Throwable $exception
     * @return bool
     */
    protected function shouldConvertException(Throwable $exception): bool
    {
        foreach (config('responder.exceptions') as $class => $error) {
            if ($exception instanceof $class) {
                return !(config('app.debug') && $error['status'] >= 500);
            }
        }

        return false;
    }

    /**
     * Convert the exception to an error message.
     *
     * @param Throwable $exception
     * @return JsonResponse
     */
    protected function convertException(Throwable $exception): JsonResponse
    {
        $responseBuilder = $this->responder->error($exception);

        if ($exception instanceof ValidationException) {
            $responseBuilder->validator($exception->validator);
        }

        return $responseBuilder->respond();
    }
}
