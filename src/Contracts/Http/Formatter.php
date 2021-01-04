<?php

namespace Flugg\Responder\Contracts\Http;

use Flugg\Responder\Http\ErrorResponse;
use Flugg\Responder\Http\SuccessResponse;

/**
 * Contract for a response formatter.
 */
interface Formatter
{
    /**
     * Format success response data.
     *
     * @param \Flugg\Responder\Http\SuccessResponse $response
     * @return array
     */
    public function success(SuccessResponse $response): array;

    /**
     * Format error response data.
     *
     * @param \Flugg\Responder\Http\ErrorResponse $response
     * @return array
     */
    public function error(ErrorResponse $response): array;
}
