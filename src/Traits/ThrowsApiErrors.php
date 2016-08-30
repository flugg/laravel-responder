<?php

namespace Flugg\Responder\Traits;

use Flugg\Responder\Exceptions\Http\UnauthorizedException;
use Flugg\Responder\Exceptions\Http\ValidationFailedException;
use Illuminate\Contracts\Validation\Validator;


/**
 * Use this trait in your base form request to override the exceptions thrown when
 * validation or authorization fails. This allows the package to render proper
 * API responses through the HandlesApiErrors trait.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
trait ThrowsApiErrors
{
    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator $validator
     * @return void
     * @throws ValidationFailedException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new ValidationFailedException($validator);
    }

    /**
     * Handle a failed authorization attempt.
     *
     * @return void
     * @throws UnauthorizedException
     */
    protected function failedAuthorization()
    {
        throw new UnauthorizedException();
    }
}