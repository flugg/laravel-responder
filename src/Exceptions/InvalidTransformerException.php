<?php

namespace Flugg\Responder\Exceptions;

use Flugg\Responder\Transformers\Transformer;
use RuntimeException;

/**
 * An exception thrown when given invalid transformers.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class InvalidTransformerException extends RuntimeException
{
    /**
     * Construct the exception class.
     */
    public function __construct()
    {
        parent::__construct('Transformer must be a callable or an instance of [' . Transformer::class . '].');
    }
}