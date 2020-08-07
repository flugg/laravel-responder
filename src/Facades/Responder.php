<?php

namespace Flugg\Responder\Facades;

use Flugg\Responder\Contracts\Responder as ResponderContract;
use Illuminate\Support\Facades\Facade;

/**
 * Facade class for accessing the responder service.
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
