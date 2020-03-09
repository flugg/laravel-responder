<?php

use Flugg\Responder\Contracts\Responder;

if (!function_exists('responder')) {
    /**
     * A helper method to resolve the responder service out of the service container.
     *
     * @return Responder
     */
    function responder(): Responder
    {
        return app(Responder::class);
    }
}
