<?php

namespace Flugg\Responder\Http;

use Flugg\Responder\Contracts\Pagination\CursorPaginator;
use Flugg\Responder\Contracts\Pagination\Paginator;
use Flugg\Responder\Exceptions\InvalidStatusCodeException;
use Flugg\Responder\Http\Resources\Resource;

/**
 * Data transfer object class for a success response.
 */
class SuccessResponse extends Response
{
    /**
     * Response status code.
     *
     * @var int
     */
    protected $status = 200;

    /**
     * Resource attached to the response.
     *
     * @var \Flugg\Responder\Http\Resources\Resource
     */
    protected $resource;

    /**
     * Paginator attached to the response.
     *
     * @var \Flugg\Responder\Contracts\Pagination\Paginator|null
     */
    protected $paginator = null;

    /**
     * Cursor paginator attached to the response.
     *
     * @var \Flugg\Responder\Contracts\Pagination\CursorPaginator|null
     */
    protected $cursor = null;

    /**
     * Create a new response instance.
     *
     * @param \Flugg\Responder\Http\Resources\Resource $resource
     */
    public function __construct(Resource $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Get the response resource.
     *
     * @return \Flugg\Responder\Http\Resources\Resource
     */
    public function resource(): Resource
    {
        return $this->resource;
    }

    /**
     * Get the paginator attached to the response.
     *
     * @return \Flugg\Responder\Contracts\Pagination\Paginator|null
     */
    public function paginator(): ?Paginator
    {
        return $this->paginator;
    }

    /**
     * Get the cursor paginator attached to the response.
     *
     * @return \Flugg\Responder\Contracts\Pagination\CursorPaginator|null
     */
    public function cursor(): ?CursorPaginator
    {
        return $this->cursor;
    }

    /**
     * Set the response status code.
     *
     * @param int $status
     * @throws \Flugg\Responder\Exceptions\InvalidStatusCodeException
     * @return $this
     */
    public function setStatus(int $status)
    {
        if ($status < 100 || $status >= 400) {
            throw new InvalidStatusCodeException;
        }

        return parent::setStatus($status);
    }

    /**
     * Set the response resource.
     *
     * @param \Flugg\Responder\Http\Resources\Resource $resource
     * @return $this
     */
    public function setResource(Resource $resource)
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * Set the paginator attached to the response.
     *
     * @param \Flugg\Responder\Contracts\Pagination\Paginator $paginator
     * @return $this
     */
    public function setPaginator(Paginator $paginator)
    {
        $this->paginator = $paginator;

        return $this;
    }

    /**
     * Set the cursor paginator attached to the response.
     *
     * @param \Flugg\Responder\Contracts\Pagination\CursorPaginator $cursor
     * @return $this
     */
    public function setCursor(CursorPaginator $cursor)
    {
        $this->cursor = $cursor;

        return $this;
    }
}
