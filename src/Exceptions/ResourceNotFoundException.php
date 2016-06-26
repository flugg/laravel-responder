<?php

namespace Mangopixel\Responder\Exceptions;

class ResourceNotFoundException extends ApiException
{
    /**
     * The HTTP status code.
     *
     * @var int
     */
    protected $statusCode = 404;

    /**
     * The error code used for API responses.
     *
     * @var string
     */
    protected $errorCode = 'resource_not_found';
}