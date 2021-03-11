<?php

namespace Flugg\Responder\Http\Resources;

/**
 * Class for a resource, representing an entity in the response data.
 */
class Item extends Resource
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
    public function __construct(array $data = [], ?string $key = null, array $relations = [])
    {
        $this->data = $data;
        $this->key = $key;
        $this->relations = $relations;
    }

    /**
     * Determine if an attribute exists on the resource.
     *
     * @param string $key
     * @return bool
     */
    public function __isset(string $key): bool
    {
        return key_exists($key, array_merge($this->data(), $this->relations()));
    }

    /**
     * Dynamically get attributes from the resource's data, including relations.
     *
     * @param string $key
     * @return mixed
     */
    public function __get(string $key)
    {
        return array_merge($this->data(), $this->relations())[$key];
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
}
