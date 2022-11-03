<?php

namespace Flugg\Responder\Exceptions;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

/**
 * An exception thrown when the given serializer is not a valid serializer class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class InvalidTransformerException extends RuntimeException
{
    /**
     * Create a new exception instance.
     */
    public function __construct()
    {
        parent::__construct('Invalid transformer given to responder.');
    }
}