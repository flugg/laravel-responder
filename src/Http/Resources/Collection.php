<?php

namespace Flugg\Responder\Http\Resources;

/**
 * Class for a resource collection, representing a list of entities in the response data.
 */
class Collection extends Resource
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
        $this->items = $items;
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
     * Convert the resource collection to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return array_map(function ($item) {
            return $item->toArray();
        }, $this->items());
    }
}
