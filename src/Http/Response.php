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
     * Additional metadata attached to the response data.
     *
     * @var array
     */
    protected $meta = [];

    /**
     * Get the response status code.
     *
     * @return int
     */
    public function status(): int
    {
        return $this->status;
    }

    /**
     * Get the response headers.
     *
     * @return array
     */
    public function headers(): array
    {
        return $this->headers;
    }

    /**
     * Get the metadata attached to the response.
     *
     * @return array
     */
    public function meta(): array
    {
        return $this->meta;
    }

    /**
     * Set the response status code.
     *
     * @param int $status
     * @throws \Flugg\Responder\Exceptions\InvalidStatusCodeException
     * @return self
     */
    public function setStatus(int $status): self
    {
        if (! $this->isValidStatusCode($status)) {
            throw new InvalidStatusCodeException;
        }

        $this->status = $status;

        return $this;
    }

    /**
     * Set the response headers.
     *
     * @param array $headers
     * @return self
     */
    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * Set metadata attached to the response.
     *
     * @param array $meta
     * @return self
     */
    public function setMeta(array $meta): self
    {
        $this->meta = $meta;

        return $this;
    }

    /**
     * Check if the status code is valid.
     *
     * @param int $status
     * @return bool
     */
    abstract protected function isValidStatusCode(int $status): bool;
}
