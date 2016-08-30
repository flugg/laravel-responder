<?php

namespace Flugg\Responder\Exceptions\Http;

/**
 * An exception replacing Laravel's \Illuminate\Auth\Access\AuthorizationException.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
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