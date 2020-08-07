<?php

namespace Flugg\Responder\Http;

use Flugg\Responder\Contracts\Pagination\CursorPaginator;
use Flugg\Responder\Contracts\Pagination\Paginator;

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
     * @var \Flugg\Responder\Http\Resource
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
    protected $cursorPaginator = null;

    /**
     * Get the response resource.
     *
     * @return \Flugg\Responder\Http\Resource
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
    public function cursorPaginator(): ?CursorPaginator
    {
        return $this->cursorPaginator;
    }

    /**
     * Set the response resource.
     *
     * @param \Flugg\Responder\Http\Resource $resource
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
    public function setCursorPaginator(CursorPaginator $cursorPaginator)
    {
        $this->cursorPaginator = $cursorPaginator;

        return $this;
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
