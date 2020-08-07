<?php

namespace Flugg\Responder\Contracts\Http;

use Flugg\Responder\Http\SuccessResponse;

/**
 * Contract for a response data normalizer.
 */
interface Normalizer
{
    /**
     * Normalize response data.
     *
     * @param object $data
     * @return \Flugg\Responder\Http\SuccessResponse
     */
    public function normalize(object $data): SuccessResponse;
}
