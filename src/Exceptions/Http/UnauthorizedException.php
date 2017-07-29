<?php

namespace Flugg\Responder\Exceptions\Http;

/**
 * An exception thrown whan a user is unauthorized. This exception replaces Laravel's
 * [\Illuminate\Auth\Access\AuthorizationException].
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class UnauthorizedException extends HttpException
{
    /**
     * An HTTP status code.
     *
     * @var int
     */
    protected $status = 403;

    /**
     * An error code.
     *
     * @var string|null
     */
    protected $errorCode = 'unauthorized';
}