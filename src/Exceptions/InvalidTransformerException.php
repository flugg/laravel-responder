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
     * InvalidSerializerException constructor.
     *
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        return parent::__construct('The given transformer does not exist for model [' . get_class($model) . '].');
    }
}