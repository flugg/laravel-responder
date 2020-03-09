<?php

namespace Flugg\Responder\Http;

use Flugg\Responder\Exceptions\InvalidStatusCodeException;

/**
 * An abstract class holding information about a response.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
abstract class Response
{
    /**
     * Response status code.
     *
     * @var int
     */
    protected $status;

    /**
     * Response headers.
     *
     * @var array
     */
    protected $headers = [];

    /**
     * Set response status code.
     *
     * @param int $status
     * @return $this
     * @throws InvalidStatusCodeException
     */
    public function setStatus(int $status)
    {
        if (!$this->isValidStatusCode($status)) {
            throw new InvalidStatusCodeException();
        }

        $this->status = $status;

        return $this;
    }

    /**
     * Set response headers.
     *
     * @param array $headers
     * @return $this
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * Get response status code.
     *
     * @return int
     */
    public function status()
    {
        return $this->status;
    }

    /**
     * Get response headers.
     *
     * @return array
     */
    public function headers()
    {
        return $this->headers;
    }

    /**
     * Verify that the status code is valid.
     *
     * @param int $status
     * @return bool
     */
    abstract protected function isValidStatusCode(int $status): bool;
}
