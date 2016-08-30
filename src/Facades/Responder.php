<?php

namespace Flugg\Responder\Facades;

use Flugg\Responder\Responder as ResponderService;
use Illuminate\Support\Facades\Facade;

/**
 * A facade you can register in config/app.php to quickly get access to the responder.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
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
        return ResponderService::class;
    }
}