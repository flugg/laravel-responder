<?php

namespace Flugg\Responder\Http\Resources;

use ArrayAccess;

/**
 * Class for a resource, representing an entitiy in the response data.
 */
class Item extends Resource implements ArrayAccess
{
    /**
     * Resource data.
     *
     * @var array
     */
    protected $data;

    /**
     * Map of nested resources.
     *
     * @var array
     */
    protected $relations = [];

    /**
     * Create a new resource item instance.
     *
     * @param array $data
     * @param string|null $key
     * @param array $relations
     */
    public function __construct(array $data = [], string $key = null, array $relations = [])
    {
        $this->data = $data;
        $this->key = $key;
        $this->relations = $relations;
    }

    /**
     * Get resource data.
     *
     * @return array
     */
    public function data(): array
    {
        return $this->data;
    }

    /**
     * Get a map of nested resources.
     *
     * @return array
     */
    public function relations(): array
    {
        return $this->relations;
    }

    /**
     * Set the resource data.
     *
     * @param array $data
     * @return $this
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Set a map of nested resources.
     *
     * @param array $relations
     * @return $this
     */
    public function setRelations(array $relations)
    {
        $this->relations = $relations;

        return $this;
    }

    /**
     * Convert the resource item to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->data();
    }

    /**
     * Determine if the given attribute exists.
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->data[$offset]);
    }

    /**
     * Get the value for a given offset.
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    /**
     * Set the value for a given offset.
     *
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    /**
     * Unset the value for a given offset.
     *
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }
}
