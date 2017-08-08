<?php

namespace Flugg\Responder\Exceptions;

use Flugg\Responder\Contracts\ErrorSerializer;
use RuntimeException;

/**
 * An exception thrown when given invalid serializers.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class InvalidErrorSerializerException extends RuntimeException
{
    /**
     * Construct the exception class.
     */
    public function __construct()
    {
        parent::__construct('Serializer must be an instance of [' . ErrorSerializer::class . '].');
    }
}