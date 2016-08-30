<?php

namespace Flugg\Responder\Exceptions;

use League\Fractal\Serializer\SerializerAbstract;
use RuntimeException;

/**
 * An exception thrown when the given serializer is not a valid serializer class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class InvalidSerializerException extends RuntimeException
{
    /**
     * Create a new exception instance.
     */
    public function __construct()
    {
        parent::__construct('Given serializer is not an instance of [' . SerializerAbstract::class . '].');
    }
}