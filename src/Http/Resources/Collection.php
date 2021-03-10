<?php

namespace Flugg\Responder\Http\Resources;

use ArrayAccess;

/**
 * Class for a resource collection, representing a list of entities in the response data.
 */
class Collection extends Resource implements ArrayAccess
{
    /**
     * List of resource items in collection.
     *
     * @var \Flugg\Responder\Http\Resources\Item[]
     */
    protected $items;

    /**
     * Create a new resource collection instance.
     *
     * @param \Flugg\Responder\Http\Resources\Item[] $items
     * @param string|null $key
     */
    public function __construct(array $items = [], ?string $key = null)
    {
        $this->items = array_map(function (Item $item) use ($key) {
            return $item->setKey($key);
        }, $items);

        $this->key = $key;
    }

    /**
     * Get list of resource items in collection.
     *
     * @return \Flugg\Responder\Http\Resources\Item[]
     */
    public function items(): array
    {
        return $this->items;
    }

    /**
     * Set a list of resource items in collection.
     *
     * @param \Flugg\Responder\Http\Resources\Item[] $items
     * @return $this
     */
    public function setItems(array $items)
    {
        $this->items = $items;

        return $this;
    }

    /**
     * Determine if the given attribute exists.
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->items[$offset]);
    }

    /**
     * Get the value for a given offset.
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->items[$offset];
    }

    /**
     * Set the value for a given offset.
     *
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        $this->items[$offset] = $value;
    }

    /**
     * Unset the value for a given offset.
     *
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset): void
    {
        unset($this->items[$offset]);
    }
}
