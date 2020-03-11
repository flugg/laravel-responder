<?php

namespace Flugg\Responder\Http;

/**
 * Value object class holding information about a success response.
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
     * Get the response data.
     *
     * @return array
     */
    public function data(): array
    {
        return $this->data;
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
