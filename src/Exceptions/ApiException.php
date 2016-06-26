<?php

namespace Mangopixel\Responder\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

abstract class ApiException extends HttpException
{
    /**
     * The HTTP status code.
     *
     * @var int
     */
    protected $statusCode = 500;

    /**
     * The error code used for API responses.
     *
     * @var string
     */
    protected $errorCode = 'error_occurred';

    /**
     * Constructor.
     *
     * @param mixed $message
     */
    public function __construct( $message = null )
    {
        parent::__construct( $this->statusCode, $message );
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
     * @return int
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }
}