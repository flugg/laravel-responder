<?php

namespace Flugg\Responder\Http;

/**
 * A class holding information about an error response.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class ErrorResponse extends Response
{
    /**
     * An error code representing the error.
     *
     * @var int|string
     */
    protected $errorCode;

    /**
     * A message explaining the error.
     *
     * @var string
     */
    protected $message;

    /**
     * Get error code.
     *
     * @return int|string
     */
    public function errorCode()
    {
        return $this->errorCode;
    }

    /**
     * Get error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->message;
    }

    /**
     * Set error code.
     *
     * @param int|string $errorCode
     * @return $this
     */
    public function setErrorCode($errorCode)
    {
        $this->errorCode = $errorCode;

        return $this;
    }

    /**
     * Set error message.
     *
     * @param string $message
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Verify that the status code is valid.
     *
     * @param int $status
     * @return bool
     */
    protected function isValidStatusCode(int $status): bool
    {
        return $status >= 400 && $status < 600;
    }
}
