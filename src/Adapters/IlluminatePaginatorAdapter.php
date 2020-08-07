<?php

namespace Flugg\Responder\Adapters;

use Flugg\Responder\Contracts\Pagination\Paginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Paginator adapter class for Illuminate paginators.
 */
class IlluminatePaginatorAdapter implements Paginator
{
    /**
     * Illuminate paginator class.
     *
     * @var \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    protected $paginator;

    /**
     * Create a new paginator adapter instance.
     *
     * @param \Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator
     */
    public function __construct(LengthAwarePaginator $paginator)
    {
        $this->paginator = $paginator;
    }

    /**
     * Get the current page.
     *
     * @return int
     */
    public function currentPage(): int
    {
        return $this->paginator->currentPage();
    }

    /**
     * Get the last page.
     *
     * @return int
     */
    public function lastPage(): int
    {
        return $this->paginator->lastPage();
    }

    /**
     * Get the total count of items.
     *
     * @return int
     */
    public function total(): int
    {
        return $this->paginator->total();
    }

    /**
     * Get the current count of items.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->paginator->items());
    }

    /**
     * Get the count of items per page.
     *
     * @return int
     */
    public function perPage(): int
    {
        return $this->paginator->perPage();
    }

    /**
     * Get the URL for the page.
     *
     * @param int $page
     * @return string
     */
    public function url(int $page): string
    {
        return $this->paginator->url($page);
    }
}
