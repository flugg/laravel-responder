<?php

namespace Flugg\Responder\Facades;

use Flugg\Responder\Contracts\Transformer as TransformerContract;
use Illuminate\Support\Facades\Facade;

/**
 * A facade class responsible for transforming data without serializing.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 *
 * @see \Flugg\Responder\Transformer
 */
class Transformer extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return TransformerContract::class;
    }
}