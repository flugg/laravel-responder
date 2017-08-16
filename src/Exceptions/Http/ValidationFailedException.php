<?php

namespace Flugg\Responder\Exceptions\Http;

use Illuminate\Contracts\Validation\Validator;

/**
 * An exception thrown whan validation fails. This exception replaces Laravel's
 * [\Illuminate\Validation\ValidationException].
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class ValidationFailedException extends HttpException
{
    /**
     * An HTTP status code.
     *
     * @var int
     */
    protected $status = 422;

    /**
     * An error code.
     *
     * @var string|null
     */
    protected $errorCode = 'validation_failed';

    /**
     * A validator for fetching validation messages.
     *
     * @var \Illuminate\Contracts\Validation\Validator
     */
    protected $validator;

    /**
     * Construct the exception class.
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     */
    public function __construct(Validator $validator)
    {
        $this->validator = $validator;

        parent::__construct();
    }

    /**
     * Retrieve the error data.
     *
     * @return array|null
     */
    public function data()
    {
        return ['fields' => $this->validator->getMessageBag()->toArray()];
    }
}