<?php

namespace Flugg\Responder\Contracts;

use Flugg\Responder\Contracts\Pagination\CursorPaginator;
use Flugg\Responder\Contracts\Pagination\Paginator;
use Flugg\Responder\Contracts\Validation\Validator;

/**
 * A contract for creating adapters.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
interface AdapterFactory
{
    /**
     * Make a paginator adapter if a mapping exists.
     *
     * @param mixed $instance
     * @return Paginator|null
     */
    public function makePaginator($instance): ?Paginator;

    /**
     * Make a cursor paginator adapter if a mapping exists.
     *
     * @param mixed $instance
     * @return CursorPaginator|null
     */
    public function makeCursorPaginator($instance): ?CursorPaginator;

    /**
     * Make a validator adapter if a mapping exists.
     *
     * @param mixed $instance
     * @return Validator|null
     */
    public function makeValidator($instance): ?Validator;
}
