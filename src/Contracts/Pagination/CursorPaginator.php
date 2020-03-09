<?php

namespace Flugg\Responder\Contracts\Pagination;

/**
 * A contract for a cursor paginator.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
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
