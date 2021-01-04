<?php

namespace Flugg\Responder\Adapters;

use Flugg\Responder\Contracts\Validation\Validator;
use Illuminate\Contracts\Validation\Validator as IlluminateValidator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Validator adapter class for an Illuminate validator.
 */
class IlluminateValidatorAdapter implements Validator
{
    /**
     * Illuminate validator class.
     *
     * @var \Illuminate\Contracts\Validation\Validator
     */
    protected $validator;

    /**
     * Create a new validator adapter instance.
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     */
    public function __construct(IlluminateValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Get a list of fields that failed validation.
     *
     * @return string[]
     */
    public function failed(): array
    {
        return array_keys($this->validator->failed());
    }

    /**
     * Get a list of fields and rules mapped to corresponding messages.
     *
     * @return array
     */
    public function messages(): array
    {
        return Collection::make($this->errors())->flatMap(function ($rules, $field) {
            return Collection::make($rules)->mapWithKeys(function ($rule) use ($rules, $field) {
                return ["$field.$rule" => $this->validator->errors()->get($field)[array_search($rule, $rules)]];
            });
        })->all();
    }

    /**
     * Get a list of fields mapped to a list of failed rules.
     *
     * @return array
     */
    public function errors(): array
    {
        return Collection::make($this->validator->failed())->mapWithKeys(function ($rules, $field) {
            return [$field => array_map([Str::class, 'snake'], array_keys($rules))];
        })->all();
    }
}
