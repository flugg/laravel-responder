<?php

namespace Flugg\Responder\Http;

use Flugg\Responder\Contracts\Validation\Validator;
use Flugg\Responder\Exceptions\InvalidStatusCodeException;

/**
 * Data transfer object class for an error response.
 */
class ErrorResponse extends Response
{
    /**
     * Response status code.
     *
     * @var int
     */
    protected $status = 500;

    /**
     * Error code representing the error response.
     *
     * @var int|string|null
     */
    protected $code;

    /**
     * Error message describing the error response.
     *
     * @var string|null
     */
    protected $message = null;

    /**
     * Validator attached to the response.
     *
     * @var \Flugg\Responder\Contracts\Validation\Validator
     */
    protected $validator = null;

    /**
     * Create a new response instance.
     *
     * @param int|string|null $code
     * @param string|null $message
     */
    public function __construct($code = null, ?string $message = null)
    {
        $this->code = $code;
        $this->message = $message;
    }

    /**
     * Get the error code.
     *
     * @return int|string|null
     */
    public function code()
    {
        return $this->code;
    }

    /**
     * Get the error message.
     *
     * @return string|null
     */
    public function message(): ?string
    {
        return $this->message;
    }

    /**
     * Get the validator attached to the response.
     *
     * @return \Flugg\Responder\Contracts\Validation\Validator|null
     */
    public function validator(): ?Validator
    {
        return $this->validator;
    }

    /**
     * Set the response status code.
     *
     * @param int $status
     * @throws \Flugg\Responder\Exceptions\InvalidStatusCodeException
     * @return $this
     */
    public function setStatus(int $status)
    {
        if ($status < 400 || $status >= 600) {
            throw new InvalidStatusCodeException;
        }

        return parent::setStatus($status);
    }

    /**
     * Set the error code.
     *
     * @param int|string|null $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Set the error message.
     *
     * @param string|null $message
     * @return $this
     */
    public function setMessage(?string $message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Set the validator attached to the response.
     *
     * @param \Flugg\Responder\Contracts\Validation\Validator $validator
     * @return $this
     */
    public function setValidator(Validator $validator)
    {
        $this->validator = $validator;

        return $this;
    }
}
