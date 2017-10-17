<?php

namespace Flugg\Responder\Contracts;

/**
 * A contract for resolving error messages from error codes.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
interface ErrorMessageResolver
{
    /**
     * Resolve a message from the given error code.
     *
     * @param  mixed $errorCode
     * @return string|null
     */
    public function resolve($errorCode);
}