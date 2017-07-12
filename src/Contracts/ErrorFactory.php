<?php

namespace Flugg\Responder\Contracts;

/**
 * A factory contract for creating error arrays.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
interface ErrorFactory
{
    /**
     * Make an error array from the given error code, message and error data.
     *
     * @param  string|null $errorCode
     * @param  string|null $message
     * @param  array|null  $data
     * @return array
     */
    public function make(string $errorCode = null, string $message = null, array $data = null): array;
}