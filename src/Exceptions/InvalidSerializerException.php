<?php

namespace Flugg\Responder\Exceptions;

use League\Fractal\Serializer\SerializerAbstract;
use RuntimeException;

/**
 * An exception thrown when given invalid serializers.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class InvalidSerializerException extends RuntimeException
{
    /**
     * Construct the exception class.
     */
    public function __construct()
    {
        parent::__construct('Given serializer must be an instance of [' . SerializerAbstract::class . '].');
    }
}