<?php

namespace Flugg\Responder\Contracts\Http;

/**
 * A contract for resolving messages from error codes.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
interface ErrorMessageResolver
{
    /**
     * Register error messages mapped to error codes.
     *
     * @param array|int|string $errorCode
     * @param string|null $message
     * @return void
     */
    public function register($errorCode, string $message = null): void;

    /**
     * Resolve a message from the given error code.
     *
     * @param int|string $errorCode
     * @return string|null
     */
    public function resolve($errorCode): ?string;
}
