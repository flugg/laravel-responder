<?php

namespace Flugg\Responder\Exceptions;

/**
 * An exception which replaces Laravel's ModelNotFoundException.
 *
 * @package Laravel Responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
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