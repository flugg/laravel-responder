<?php

namespace Flugg\Responder\Exceptions\Http;

use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * An abstract exception responsible for holding error response data.
 * You can convert exceptions to a JSON response by extending this class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
abstract class ApiException extends HttpException
{
    /**
     * An HTTP status code.
     *
     * @var int
     */
    protected $status = 500;

    /**
     * An error code.
     *
     * @var string|null
     */
    protected $errorCode = null;

    /**
     * An error message.
     *
     * @var string|null
     */
    protected $message = null;

    /**
     * Additional error data.
     *
     * @var array|null
     */
    protected $data = null;

    /**
     * Construct the exception class.
     *
     * @param string $message
     */
    public function __construct(string $message = null)
    {
        parent::__construct($this->status, $this->message ?? $message);
    }

    /**
     * Retrieve the HTTP status code,
     *
     * @return int
     */
    public function statusCode(): int
    {
        return $this->status;
    }

    /**
     * Retrieve the error code.
     *
     * @return string|null
     */
    public function errorCode()
    {
        return $this->errorCode;
    }

    /**
     * Retrieve the error message.
     *
     * @return string|null
     */
    public function message()
    {
        return $this->message;
    }

    /**
     * Retrieve additional error data.
     *
     * @return array|null
     */
    public function data()
    {
        return $this->data;
    }
}
