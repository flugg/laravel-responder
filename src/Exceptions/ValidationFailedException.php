<?php

namespace Flugg\Responder\Exceptions;

use Illuminate\Contracts\Validation\Validator;

/**
 * An exception which replaces Laravel's validation exception.
 *
 * @package Laravel Responder
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
     * The array of validation error messages.
     *
     * @var array
     */
    protected $validationMessages = [ ];

    /**
     * Create a new exception instance.
     *
     * @param Validator $validator
     */
    public function __construct( Validator $validator )
    {
        $this->setValidationMessages( $validator );

        parent::__construct( 'Validation has failed.' );
    }

    /**
     * Get the array of validation error messages.
     *
     * @return array
     */
    public function getValidationMessages()
    {
        return $this->validationMessages;
    }

    /**
     * Set the array of validation error messages.
     *
     * @param  Validator $validator
     * @return void
     */
    public function setValidationMessages( Validator $validator )
    {
        $messages = [ ];
        $attributes = $validator->getMessageBag()->toArray();

        foreach ( $attributes as $attribute ) {
            $messages = array_merge( $messages, $attribute );
        }

        $this->validationMessages = $messages;
    }
}