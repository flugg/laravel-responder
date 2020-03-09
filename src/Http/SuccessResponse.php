<?php

namespace Flugg\Responder\Http;

/**
 * A class holding information about a success response.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class SuccessResponse extends Response
{
    /**
     * Response data.
     *
     * @var array
     */
    protected $data;

    /**
     * Response meta data.
     *
     * @var array
     */
    protected $meta = [];

    /**
     * Set response data.
     *
     * @param array data
     * @param array $data
     * @return $this
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Set response meta data.
     *
     * @param array $meta
     * @return $this
     */
    public function setMeta($meta)
    {
        $this->meta = $meta;

        return $this;
    }

    /**
     * Get response data.
     *
     * @return array
     */
    public function data(): array
    {
        return $this->data;
    }

    /**
     * Get response meta data.
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
    protected function isValidStatusCode(int $status): bool
    {
        return $status >= 100 && $status < 400;
    }
}
