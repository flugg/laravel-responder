<?php

namespace Flugg\Responder\Exceptions\Http;

use Symfony\Component\HttpKernel\Exception\HttpException as BaseHttpException;

/**
 * An abstract exception responsible for holding error response data.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
abstract class HttpException extends BaseHttpException
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
     * @var string
     */
    protected $message = '';

    /**
     * Additional error data.
     *
     * @var array|null
     */
    protected $data = null;

    /**
     * Attached headers.
     *
     * @var array
     */
    protected $headers = [];

    /**
     * Construct the exception class.
     *
     * @param string|null $message
     * @param array|null  $headers
     */
    public function __construct(string $message = null, array $headers = null)
    {
        parent::__construct($this->status, $message ?? $this->message, null, $headers ?? $this->headers);
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
        return $this->message ?: null;
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

    /**
     * Retrieve attached headers.
     *
     * @return array|null
     */
    public function headers()
    {
        return $this->headers;
    }
}
