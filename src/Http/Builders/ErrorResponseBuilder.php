<?php

namespace Flugg\Responder\Http\Builders;

use Exception;
use Flugg\Responder\Contracts\ErrorMessageRegistry;
use Flugg\Responder\Contracts\Http\Formatter;
use Flugg\Responder\Contracts\Http\ResponseFactory;
use Flugg\Responder\Contracts\Validation\Validator;
use Flugg\Responder\Http\ErrorResponse;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Str;

/**
 * Builder class for building error responses.
 */
class ErrorResponseBuilder extends ResponseBuilder
{
    /**
     * Response value object.
     *
     * @var \Flugg\Responder\Http\ErrorResponse
     */
    protected $response;

    /**
     * Registry class for resolving error messages.
     *
     * @var \Flugg\Responder\Contracts\ErrorMessageRegistry
     */
    protected $messageRegistry;

    /**
     * Create a new response builder instance.
     *
     * @param \Flugg\Responder\Contracts\Http\ResponseFactory $responseFactory
     * @param \Flugg\Responder\Contracts\Http\Formatter $formatter
     * @param \Illuminate\Contracts\Config\Repository $config
     * @param \Illuminate\Contracts\Container\Container $container
     * @param \Flugg\Responder\Contracts\ErrorMessageRegistry $messageRegistry
     */
    public function __construct(
        ResponseFactory $responseFactory,
        Formatter $formatter,
        Repository $config,
        Container $container,
        ErrorMessageRegistry $messageRegistry
    ) {
        $this->messageRegistry = $messageRegistry;

        parent::__construct($responseFactory, $formatter, $config, $container);
    }

    /**
     * Build an error response.
     *
     * @param \Exception|int|string|null $code
     * @param \Exception|string|null $message
     * @return $this
     */
    public function make($code = null, $message = null)
    {
        if (($exception = $code) instanceof Exception) {
            $this->response = $this->makeResponseFromException($exception);
        } elseif (($exception = $message) instanceof Exception) {
            $this->response = $this->makeResponseFromException($exception, $code);
        } else {
            $this->response = (new ErrorResponse)->setCode($code)->setMessage($message);
        }

        return $this;
    }

    /**
     * Attach a validator to the error response.
     *
     * @param \Flugg\Responder\Contracts\Validation\Validator $validator
     * @return $this
     */
    public function validator(Validator $validator)
    {
        $this->response->setValidator($validator);

        return $this;
    }

    /**
     * Retrieve the response data transer object.
     *
     * @return \Flugg\Responder\Http\ErrorResponse
     */
    public function get()
    {
        return $this->response;
    }

    /**
     * Make an error response from the exception.
     *
     * @param \Exception $exception
     * @param int|string|null $code
     * @return \Flugg\Responder\Http\ErrorResponse
     */
    protected function makeResponseFromException(Exception $exception, $code = null): ErrorResponse
    {
        $error = $this->config->get('responder.exceptions')[get_class($exception)] ?? null;
        $code = $code ?: ($error['code'] ?? $this->resolveCodeFromClassName($exception));
        $message = $this->messageRegistry->resolve($code) ?: $exception->getMessage();

        return tap((new ErrorResponse)->setCode($code)->setMessage($message), function ($response) use ($error) {
            if ($error && $status = $error['status']) {
                $response->setStatus($status);
            }
        });
    }

    /**
     * Resolve an error code from an exception class name.
     *
     * @param \Exception $exception
     * @return string
     */
    protected function resolveCodeFromClassName(Exception $exception): string
    {
        return Str::snake(Str::replaceLast('Exception', '', class_basename($exception)));
    }

    /**
     * Format the response data.
     *
     * @return array
     */
    protected function data(): array
    {
        return $this->formatter->error($this->response);
    }
}
