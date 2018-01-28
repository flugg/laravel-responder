<?php

namespace Flugg\Responder\Pagination;

use Closure;
use Illuminate\Support\Collection;
use LogicException;

/**
 * A paginator class for handling cursor-based pagination.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class CursorPaginator
{
    /**
     * A list of the items being paginated.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $items;

    /**
     * The current cursor reference.
     *
     * @var int|string|null
     */
    protected $cursor;

    /**
     * The previous cursor reference.
     *
     * @var int|string|null
     */
    protected $previousCursor;

    /**
     * The next cursor reference.
     *
     * @var int|string|null
     */
    protected $nextCursor;

    /**
     * The current cursor resolver callback.
     *
     * @var \Closure|null
     */
    protected static $currentCursorResolver;

    /**
     * Create a new paginator instance.
     *
     * @param \Illuminate\Support\Collection|array|null $data
     * @param int|string|null                           $cursor
     * @param int|string|null                           $previousCursor
     * @param int|string|null                           $nextCursor
     */
    public function __construct($data, $cursor, $previousCursor, $nextCursor)
    {
        $this->cursor = $cursor;
        $this->previousCursor = $previousCursor;
        $this->nextCursor = $nextCursor;

        $this->set($data);
    }

    /**
     * Retrieve the current cursor reference.
     *
     * @return int|string|null
     */
    public function cursor()
    {
        return $this->cursor;
    }

    /**
     * Retireve the next cursor reference.
     *
     * @return int|string|null
     */
    public function previous()
    {
        return $this->previousCursor;
    }

    /**
     * Retireve the next cursor reference.
     *
     * @return int|string|null
     */
    public function next()
    {
        return $this->nextCursor;
    }

    /**
     * Get the slice of items being paginated.
     *
     * @return array
     */
    public function items(): array
    {
        return $this->items->all();
    }

    /**
     * Get the paginator's underlying collection.
     *
     * @return \Illuminate\Support\Collection
     */
    public function get(): Collection
    {
        return $this->items;
    }

    /**
     * Set the paginator's underlying collection.
     *
     * @param  \Illuminate\Support\Collection|array|null $data
     * @return self
     */
    public function set($data): CursorPaginator
    {
        $this->items = $data instanceof Collection ? $data : collect($data);

        return $this;
    }

    /**
     * Resolve the current cursor using the cursor resolver.
     *
     * @param  string $name
     * @return mixed
     * @throws \LogicException
     */
    public static function resolveCursor(string $name = 'cursor')
    {
        if (isset(static::$currentCursorResolver)) {
            return call_user_func(static::$currentCursorResolver, $name);
        }

        throw new LogicException("Could not resolve cursor with the name [{$name}].");
    }

    /**
     * Set the current cursor resolver callback.
     *
     * @param  \Closure $resolver
     * @return void
     */
    public static function cursorResolver(Closure $resolver)
    {
        static::$currentCursorResolver = $resolver;
    }
}
