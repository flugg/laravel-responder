<?php

namespace Flugg\Responder;

use Flugg\Responder\Contracts\Responder;
use Flugg\Responder\Http\Builders\ErrorResponseBuilder;
use Flugg\Responder\Http\Builders\SuccessResponseBuilder;

/**
 * Trait for building success- and error responses.
 */
trait MakesJsonResponses
{
    /**
     * Build a success response.
     *
     * @param mixed $data
     * @return \Flugg\Responder\Http\Builders\SuccessResponseBuilder
     */
    public function success($data = null): SuccessResponseBuilder
    {
        return app(Responder::class)->success($data);
    }

    /**
     * Build an error response.
     *
     * @param int|string|\Exception|null $code
     * @param string|\Exception|null $message
     * @return \Flugg\Responder\Http\Builders\ErrorResponseBuilder
     */
    public function error($code = null, $message = null): ErrorResponseBuilder
    {
        return app(Responder::class)->error($code, $message);
    }
}
