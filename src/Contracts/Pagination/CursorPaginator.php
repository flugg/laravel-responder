<?php

namespace Flugg\Responder\Contracts\Pagination;

/**
 * Contract for a cursor paginator.
 */
interface CursorPaginator
{
    /**
     * Get the current cursor.
     *
     * @return mixed
     */
    public function current();

    /**
     * Get the previous cursor.
     *
     * @return mixed
     */
    public function previous();

    /**
     * Get the next cursor.
     *
     * @return mixed
     */
    public function next();

    /**
     * Get the current count of items.
     *
     * @return int
     */
    public function count(): int;
}
