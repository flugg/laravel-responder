<?php

namespace Flugg\Responder\Http;

/**
 * A value object class holding information about a success response.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class SuccessResponse extends Response
{
    /**
     * Data attached to the success response.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Additional meta data attached to the success response.
     *
     * @var array|null
     */
    protected $meta = null;

    /**
     * Set the response data.
     *
     * @param array $data
     *
     * @return $this
     */
    public function setData(array $data)
    {
        $this->data = $data;

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
     * Get the response data.
     *
     * @return array
     */
    public function data(): array
    {
        return $this->data;
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
    protected function isValidStatusCode(int $status): bool
    {
        return $status >= 100 && $status < 400;
    }
}
