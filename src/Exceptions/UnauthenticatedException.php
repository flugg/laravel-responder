<?php

namespace Flugg\Responder\Exceptions;

/**
 * An exception which replaces Laravel's own UnauthorizedException.
 *
 * @package Laravel Responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class UnauthenticatedException extends ApiException
{
    /**
     * The HTTP status code.
     *
     * @var int
     */
    protected $statusCode = 401;

    /**
     * The error code used for API responses.
     *
     * @var string
     */
    protected $errorCode = 'unauthenticated';
}