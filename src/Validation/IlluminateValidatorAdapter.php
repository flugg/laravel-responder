<?php

namespace Flugg\Responder\Validation;

use Flugg\Responder\Contracts\Validation\Validator;
use Illuminate\Contracts\Validation\Validator as IlluminateValidator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * A paginator adapter class for Laravel's validator.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class IlluminateValidatorAdapter implements Validator
{
    /**
     * The validator instance.
     *
     * @var IlluminateValidator
     */
    protected $validator;

    /**
     * Construct the class.
     *
     * @param IlluminateValidator $validator
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
     * Get a list of fields mapped to a list of the failed rules.
     *
     * @return array
     */
    public function errors(): array
    {
        return Collection::make($this->validator->failed())->mapWithKeys(function ($rules, $field) {
            return [$field => array_map([Str::class, 'snake'], array_keys($rules))];
        })->all();
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
}
