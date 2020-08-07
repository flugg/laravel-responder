<?php

namespace Flugg\Responder\Exceptions;

use Flugg\Responder\Adapters\IlluminateValidatorAdapter;
use Flugg\Responder\Contracts\Responder;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Exception handler decorating an existing handler with additional conversion logic.
 */
class Handler implements ExceptionHandler
{
    /**
     * Decorated exception handler.
     *
     * @var \Illuminate\Contracts\Debug\ExceptionHandler
     */
    protected $handler;

    /**
     * Config repository.
     *
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    /**
     * Responder service for making error responses.
     *
     * @var \Flugg\Responder\Contracts\Responder
     */
    protected $responder;

    /**
     * Create a new exception handler instance.
     *
     * @param \Illuminate\Contracts\Debug\ExceptionHandler $handler
     * @param \Illuminate\Contracts\Config\Repository $config
     * @param \Flugg\Responder\Contracts\Responder $responder
     */
    public function __construct(ExceptionHandler $handler, Repository $config, Responder $responder)
    {
        $this->handler = $handler;
        $this->config = $config;
        $this->responder = $responder;
    }

    /**
     * Report or log an exception.
     *
     * @param \Throwable $exception
     * @return void
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        return $this->handler->report($exception);
    }

    /**
     * Determine if the exception should be reported.
     *
     * @param \Throwable $exception
     * @return bool
     */
    public function shouldReport(Throwable $exception)
    {
        return $this->handler->shouldReport($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Throwable $exception
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Throwable
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
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Throwable $exception
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
     * @param \Throwable $exception
     * @return bool
     */
    protected function shouldConvertException(Throwable $exception): bool
    {
        foreach ($this->config->get('responder.exceptions') as $class => $error) {
            if ($exception instanceof $class) {
                return !($this->config->get('app.debug') && $error['status'] >= 500);
            }
        }

        return false;
    }

    /**
     * Convert the exception to an error message.
     *
     * @param \Throwable $exception
     * @return \Illuminate\Http\JsonResponse
     */
    protected function convertException(Throwable $exception): JsonResponse
    {
        $responseBuilder = $this->responder->error($exception);

        if ($exception instanceof ValidationException) {
            $responseBuilder->validator(new IlluminateValidatorAdapter($exception->validator));
        }

        return $responseBuilder->respond();
    }
}
