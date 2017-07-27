<?php

namespace Flugg\Responder\Contracts;

/**
 * A contract for formatting error arrays.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
interface ErrorSerializer
{
    /**
     * Format the error data.
     *
     * @param  string|null $errorCode
     * @param  string|null $message
     * @param  array|null  $data
     * @return array
     */
    public function format(string $errorCode = null, string $message = null, array $data = null): array;
}