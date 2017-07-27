<?php

namespace Flugg\Responder\Contracts\Pagination;

use Flugg\Responder\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use League\Fractal\Pagination\Cursor;
use League\Fractal\Pagination\PaginatorInterface;

/**
 * A contract for creating pagination adapters.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
interface PaginatorFactory
{
    /**
     * Make a Fractal paginator adapter from a Laravel paginator.
     *
     * @param  \Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator
     * @return \League\Fractal\Pagination\PaginatorInterface
     */
    public function make(LengthAwarePaginator $paginator): PaginatorInterface;

    /**
     * Make a Fractal paginator adapter from a Laravel paginator.
     *
     * @param  \Flugg\Responder\Pagination\CursorPaginator $paginator
     * @return \League\Fractal\Pagination\Cursor
     */
    public function makeCursor(CursorPaginator $paginator): Cursor;
}