<?php

namespace Flugg\Responder\Exceptions\Http;

/**
 * An exception replacing Laravel's Illuminate\Database\Eloquent\RelationNotFoundException.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class RelationNotFoundException extends ApiException
{
    /**
     * The HTTP status code.
     *
     * @var int
     */
    protected $statusCode = 422;

    /**
     * The error code used for API responses.
     *
     * @var string
     */
    protected $errorCode = 'relation_not_found';
}