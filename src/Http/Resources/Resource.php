<?php

namespace Flugg\Responder\Http\Resources;

/**
 * Abstract data transfer object class for a resource item or collection.
 */
abstract class Resource
{
    /**
     * Resource key.
     *
     * @var string|null
     */
    protected $key;

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
     * Convert the resource to an array.
     *
     * @return array
     */
    abstract public function toArray(): array;
}
