<?php

namespace Flugg\Responder\Contracts\Pagination;

/**
 * A contract for a paginator.
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
     * Get the total number of items.
     *
     * @return int
     */
    public function total(): int;
    
    /**
     * Get the current number of items.
     *
     * @return int
     */
    public function count(): int;

    /**
     * Get the number per page.
     *
     * @return int
     */
    public function perPage(): int;
    
    /**
     * Get the url for the given page.
     *
     * @param int $page
     * @return string
     */
    public function url(int $page): string;
}
