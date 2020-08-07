<?php

namespace Flugg\Responder\Http;

/**
 * Data transfer object class for a resource, which is a representation of an entitiy in the response data.
 */
class Resource
{
    /**
     * Resource data.
     *
     * @var array
     */
    protected $data;

    /**
     * Resource key.
     *
     * @var string|null
     */
    protected $key;

    /**
     * List of nested resources.
     *
     * @var \Flugg\Responder\Http\Resource[]
     */
    protected $relations = [];

    /**
     * Create a new resource instance.
     *
     * @param array $data
     * @param string|null $key
     * @param \Flugg\Responder\Http\Resource[] $relations
     */
    public function __construct(array $data, string $key = null, $relations = [])
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
     * Get resource key.
     *
     * @return string|null
     */
    public function key(): ?string
    {
        return $this->key;
    }

    /**
     * Get a list of nested resources.
     *
     * @return \Flugg\Responder\Http\Resource[]
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
     * Set the resource key.
     *
     * @param string|null $key
     * @return $this
     */
    public function setKey(?string $key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Set a list of nested resources.
     *
     * @param \Flugg\Responder\Http\Resource[] $relations
     * @return $this
     */
    public function setRelations(array $relations)
    {
        $this->relations = $relations;

        return $this;
    }

    /**
     * Add a nested resource.
     *
     * @param \Flugg\Responder\Http\Resource[] $relations
     * @return $this
     */
    public function addRelation(Resource $relation)
    {
        $this->relations[] = $relation;

        return $this;
    }
}
