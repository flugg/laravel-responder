<?php

namespace Flugg\Responder\Facades;

use Flugg\Responder\Transformation as TransformationService;
use Illuminate\Support\Facades\Facade;

/**
 * A facade class responsible for giving easy access to the transformer service.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 *
 * @see \Flugg\Responder\Transformer
 */
class Transformation extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return TransformationService::class;
    }
}