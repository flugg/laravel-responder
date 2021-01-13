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
    public function success($data = null): SuccessResponseBuilder;

    /**
     * Build an error response.
     *
     * @param \Exception|int|string|null $code
     * @param \Exception|string|null $message
     * @return \Flugg\Responder\Http\Builders\ErrorResponseBuilder
     */
    public function error($code = null, $message = null): ErrorResponseBuilder;
}
