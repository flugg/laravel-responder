<?php

namespace Flugg\Responder\Testing;

use Flugg\Responder\Contracts\Responder;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\ValidationRuleParser;
use Illuminate\Validation\Validator;

/**
 * A mixin class extending TestResponse providing an [assertValidationErrors] method.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class AssertValidationErrorsMacro
{
    /**
     * An invokable function returning a macro callable.
     *
     * @return callable
     */
    public function __invoke(): callable
    {
        return function ($validations) {
            $validator = app(Validator::class, ['data' => [], 'rules' => []]);

            foreach ($validations as $attribute => $rules) {
                foreach (Arr::wrap($rules) as $rule) {
                    [$rule, $parameters] = ValidationRuleParser::parse($rule);
                    $validator->addFailure($attribute, $rule, $parameters);
                }
            }

            $this->assertExactJson(app(Responder::class)
                ->error(new ValidationException($validator))
                ->validator($validator)
                ->respond($this->getStatusCode())->getData(true));

            return $this;
        };
    }
}
