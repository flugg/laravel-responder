<?php

namespace Flugg\Responder\Contracts;

use Flugg\Responder\Http\Builders\ErrorResponseBuilder;
use Flugg\Responder\Http\Builders\SuccessResponseBuilder;

/**
 * Contract for building success- and error responses.
 */
interface Responder
{
    /**
     * Build a success response.
     *
     * @param mixed $data
     * @return \Flugg\Responder\Http\Builders\SuccessResponseBuilder
     */
    public function success($data = []): SuccessResponseBuilder;

    /**
     * Build an error response.
     *
     * @param int|string|\Exception|null $code
     * @param string|\Exception|null $message
     * @return \Flugg\Responder\Http\Builders\ErrorResponseBuilder
     */
    public function error($code = null, $message = null): ErrorResponseBuilder;
}
