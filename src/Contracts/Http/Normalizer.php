<?php

namespace Flugg\Responder\Contracts\Http;

use Flugg\Responder\Http\SuccessResponse;

/**
 * Contract for a response normalizer.
 */
interface Normalizer
{
    /**
     * Normalize response data.
     *
     * @return \Flugg\Responder\Http\SuccessResponse
     */
    public function normalize(): SuccessResponse;
}
