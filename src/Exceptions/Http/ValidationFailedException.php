<?php

namespace Flugg\Responder\Exceptions\Http;

use Illuminate\Contracts\Validation\Validator;

/**
 * An exception replacing Laravel's \Illuminate\Validation\ValidationException.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class ValidationFailedException extends ApiException
{
    /**
     * The HTTP status code.
     *
     * @var int
     */
    protected $statusCode = 422;

    /**
     * The error code used for API responses.
     *
     * @var string
     */
    protected $errorCode = 'validation_failed';

    /**
     * The validator instance.
     *
     * @var Validator
     */
    protected $validator;

    /**
     * Create a new exception instance.
     *
     * @param Validator $validator
     */
    public function __construct(Validator $validator)
    {
        $this->validator = $validator;

        parent::__construct();
    }

    /**
     * Get the error data.
     *
     * @return array|null
     */
    public function getData()
    {
        return ['fields' => $this->validator->getMessageBag()->toArray()];
    }
}