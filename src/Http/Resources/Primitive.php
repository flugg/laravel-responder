<?php

namespace Flugg\Responder\Http\Resources;

/**
 * Class for a resource, representing a primitive in the response data.
 */
class Primitive extends Resource
{
    /**
     * Resource data.
     *
     * @var bool|float|int|string
     */
    protected $data;

    /**
     * Create a new resource item instance.
     *
     * @param bool|float|int|string $data
     * @param string|null $key
     */
    public function __construct($data = [], ?string $key = null)
    {
        $this->data = $data;
        $this->key = $key;
    }

    /**
     * Get resource data.
     *
     * @return bool|float|int|string
     */
    public function data()
    {
        return $this->data;
    }

    /**
     * Set the resource data.
     *
     * @param bool|float|int|string $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }
}
