<?php

namespace Flugg\Responder\Contracts\Pagination;

/**
 * Contract for a paginator.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
interface Paginator
{
    /**
     * Get the current page.
     *
     * @return int
     */
    public function currentPage(): int;

    /**
     * Get the last page.
     *
     * @return int
     */
    public function lastPage(): int;

    /**
     * Get the total count of items.
     *
     * @return int
     */
    public function total(): int;

    /**
     * Get the current count of items.
     *
     * @return int
     */
    public function count(): int;

    /**
     * Get the count of items per page.
     *
     * @return int
     */
    public function perPage(): int;

    /**
     * Get the URL for the given page.
     *
     * @param int $page
     * @return string
     */
    public function url(int $page): string;
}
