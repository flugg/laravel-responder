<?php

namespace Flugg\Responder\Contracts\Http\Builders;

use Exception;
use Flugg\Responder\Exceptions\MissingAdapterException;

/**
 * A contract for building error responses.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
interface ErrorResponseBuilder extends ResponseBuilder
{
    /**
     * Make an error response from an error code and message.
     *
     * @param Exception|int|string|null $errorCode
     * @param Exception|string|null $message
     * @return $this
     */
    public function error($errorCode = null, $message = null);

    /**
     * Add a validator to the error response.
     *
     * @param mixed $validator
     * @return $this
     * @throws MissingAdapterException
     */
    public function validator($validator);
}
