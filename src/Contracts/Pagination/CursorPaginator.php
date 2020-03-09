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
     * Get the current page.
     *
     * @return int
     */
    public function current(): int;

    /**
     * Get the last page.
     *
     * @return int
     */
    public function previous(): int;

    /**
     * Get the total number of items.
     *
     * @return int
     */
    public function next(): int;

    /**
     * Get the current number of items.
     *
     * @return int
     */
    public function count(): int;
}
