<?php

namespace Mangopixel\Responder\Exceptions;

class UnauthorizedException extends ApiException
{
    /**
     * The HTTP status code.
     *
     * @var int
     */
    protected $statusCode = 403;

    /**
     * The error code used for API responses.
     *
     * @var string
     */
    protected $errorCode = 'unauthorized';
}