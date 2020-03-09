<?php

namespace Flugg\Responder\Contracts\Validation;

/**
 * A contract for a validator.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
interface Validator
{
    /**
     * Get a list of fields that failed validation.
     *
     * @return string[]
     */
    public function failed(): array;

    /**
     * Get a list of fields mapped to a list of the failed rules.
     *
     * @return array
     */
    public function errors(): array;

    /**
     * Get a list of fields and rules mapped to corresponding messages.
     *
     * @return array
     */
    public function messages(): array;
}
