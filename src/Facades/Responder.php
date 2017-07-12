<?php

namespace Flugg\Responder\Facades;

use Flugg\Responder\Contracts\Responder as ResponderContract;
use Illuminate\Support\Facades\Facade;

/**
 * A facade class responsible for giving easy access to the responder service.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 *
 * @see \Flugg\Responder\Responder
 */
class Responder extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return ResponderContract::class;
    }
}