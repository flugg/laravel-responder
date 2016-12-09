<?php

namespace Flugg\Responder\Exceptions\Http;

use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * An abstract base exception used for any API related exceptions. All exceptions thrown
 * that extends this class will automatically be converted to JSON responses if using
 * the \Flugg\Responder\Traits\HandlesApiErrors trait in your exception handler.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
abstract class ApiException extends HttpException
{
    /**
     * The HTTP status code.
     *
     * @var int
     */
    protected $statusCode = 500;

    /**
     * The error code.
     *
     * @var string
     */
    protected $errorCode = 'error_occurred';

    /**
     * The error message.
     *
     * @var string
     */
    protected $message;

    /**
     * Create a new exception instance.
     *
     * @param mixed $message
     */
    public function __construct($message = null)
    {
        parent::__construct($this->statusCode, $this->message ?? $message);
    }

    /**
     * Get the HTTP status code,
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Get the error code.
     *
     * @return string
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * Get the error data.
     *
     * @return array|null
     */
    public function getData()
    {
        return null;
    }
}
