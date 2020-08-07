<?php

namespace Flugg\Responder\Testing;

use Flugg\Responder\Adapters\IlluminateValidatorAdapter;
use Flugg\Responder\Contracts\Responder;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\ValidationRuleParser;
use Illuminate\Validation\Validator;

/**
 * Mixin class extending [Illuminate\Testing\TestResponse] with an [assertValidationErrors] method.
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

            $this->assertExactJson(
                app(Responder::class)
                    ->error(new ValidationException($validator))
                    ->validator(new IlluminateValidatorAdapter($validator))
                    ->respond($this->getStatusCode())->getData(true)
            );

            return $this;
        };
    }
}
