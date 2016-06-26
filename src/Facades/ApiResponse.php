<?php

namespace Mangopixel\Responder\Facades;

use Illuminate\Support\Facades\Facade;
use Mangopixel\Responder\Contracts\Responder;

/**
 *
 *
 * @package Laravel Responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class ApiResponse extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Responder::class;
    }
}