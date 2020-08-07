<?php

use Flugg\Responder\Contracts\Responder;

if (!function_exists('responder')) {
    /**
     * Helper function to resolve the responder service out of the service container.
     *
     * @return \Flugg\Responder\Contracts\Responder
     */
    function responder(): Responder
    {
        return app(Responder::class);
    }
}
