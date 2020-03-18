<?php

namespace Flugg\Responder\Contracts\Http;

use Flugg\Responder\Http\SuccessResponse;

/**
 * Contract for a response data normalizer.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
interface Normalizer
{
    /**
     * Normalize response data.
     *
     * @param object $data
     * @return \Flugg\Responder\Http\SuccessResponse
     */
    public function normalize($data): SuccessResponse;
}
