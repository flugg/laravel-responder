<?php

namespace Flugg\Responder\Contracts\Validation;

/**
 * Contract for a validator.
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
     * Get a list of fields mapped to a list of failed rules.
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
