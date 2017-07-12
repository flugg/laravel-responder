<?php

namespace Flugg\Responder\Exceptions\Http;

/**
 * An exception thrown whan a resource is not found. This exception replaces
 * [\Illuminate\Database\Eloquent\ModelNotFoundException].
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class ResourceNotFoundException extends ApiException
{
    /**
     * An HTTP status code.
     *
     * @var int
     */
    protected $status = 404;

    /**
     * An error code.
     *
     * @var string|null
     */
    protected $errorCode = 'resource_not_found';
}