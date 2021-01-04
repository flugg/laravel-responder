<?php

namespace Flugg\Responder\Http;

use Flugg\Responder\Contracts\Validation\Validator;

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
     * @var int|string
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
     * Get the error code.
     *
     * @return int|string
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
     * Set the error code.
     *
     * @param int|string $code
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

    /**
     * Check if the status code is valid.
     *
     * @param int $status
     * @return bool
     */
    protected function isValidStatusCode(int $status): bool
    {
        return $status >= 400 && $status < 600;
    }
}
