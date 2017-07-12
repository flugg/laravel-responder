<?php

namespace Flugg\Responder\Pagination;

use League\Fractal\Pagination\Cursor;

/**
 * A factory class for making Fractal cursors from a cursor paginator.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class CursorFactory
{
    /**
     * A list of query string values appended to the cursor links.
     *
     * @var array
     */
    protected $parameters;

    /**
     * Construct the factory class.
     *
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * Make a Fractal cursor from a cursor paginator.
     *
     * @param  \Flugg\Responder\Pagination\CursorPaginator $paginator
     * @return \League\Fractal\Pagination\Cursor
     */
    public function make(CursorPaginator $paginator): Cursor
    {
        $paginator->appends($this->parameters);

        return new Cursor($paginator->cursor(), $paginator->previousCursor(), $paginator->nextCursor(), $paginator->getCollection()->count());
    }
}
