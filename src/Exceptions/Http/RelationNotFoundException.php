<?php

namespace Flugg\Responder\Exceptions\Http;

/**
 * An exception thrown whan a relation is not found.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class RelationNotFoundException extends HttpException
{
    /**
     * An HTTP status code.
     *
     * @var int
     */
    protected $status = 422;

    /**
     * An error code.
     *
     * @var string|null
     */
    protected $errorCode = 'relation_not_found';
}