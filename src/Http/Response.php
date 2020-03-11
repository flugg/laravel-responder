<?php

namespace Flugg\Responder\Http;

use Flugg\Responder\Exceptions\InvalidStatusCodeException;

/**
 * Abstract value object class holding information about a response.
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
     * Additional meta data attached to the response data.
     *
     * @var array
     */
    protected $meta = [];

    /**
     * Set the response status code.
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
     * Set the response headers.
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
     * Set the meta data.
     *
     * @param array $meta
     * @return $this
     */
    public function setMeta(array $meta)
    {
        $this->meta = $meta;

        return $this;
    }

    /**
     * Get the response status code.
     *
     * @return int
     */
    public function status()
    {
        return $this->status;
    }

    /**
     * Get the response headers.
     *
     * @return array
     */
    public function headers()
    {
        return $this->headers;
    }

    /**
     * Get the meta data.
     *
     * @return array
     */
    public function meta()
    {
        return $this->meta;
    }

    /**
     * Verify that the status code is valid.
     *
     * @param int $status
     * @return bool
     */
    abstract protected function isValidStatusCode(int $status): bool;
}
