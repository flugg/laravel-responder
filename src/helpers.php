<?php

use Flugg\Responder\Responder;

if (! function_exists('responder')) {

    /**
     * A helper method to quickly resolve the responder out of the service container.
     *
     * @return Responder
     */
    function responder()
    {
        return app(Responder::class);
    }
}