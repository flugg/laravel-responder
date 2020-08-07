<?php

namespace Flugg\Responder\Http;

use Flugg\Responder\Exceptions\InvalidStatusCodeException;

/**
 * Abstract data transfer object class for a response.
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
     * Get the meta data attached to the response.
     *
     * @return array
     */
    public function meta()
    {
        return $this->meta;
    }

    /**
     * Set the response status code.
     *
     * @param int $status
     * @return $this
     * @throws \Flugg\Responder\Exceptions\InvalidStatusCodeException
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
     * Set meta data attached to the response.
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
     * Verify that the status code is valid.
     *
     * @param int $status
     * @return bool
     */
    abstract protected function isValidStatusCode(int $status): bool;
}
