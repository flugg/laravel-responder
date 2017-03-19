<?php

namespace Flugg\Responder\Pagination;

use Closure;
use Illuminate\Support\Collection;

class CursorPaginator
{
    /**
     * All of the items being paginated.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $items;

    /**
     * The current cursor reference.
     *
     * @var int
     */
    protected $cursor;

    /**
     * The next cursor reference.
     *
     * @var int
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
     * @param  mixed $items
     * @param  int   $cursor
     * @param  int   $nextCursor
     */
    public function __construct($items, $cursor, $nextCursor)
    {
        $this->cursor = $cursor;
        $this->nextCursor = $nextCursor;
        $this->items = $items instanceof Collection ? $items : Collection::make($items);
    }

    /**
     * Get the total number of items being paginated.
     *
     * @return int
     */
    public function total()
    {
        return $this->total;
    }

    /**
     * Get the total number of items being paginated.
     *
     * @return int
     */
    public function cursor()
    {
        return $this->cursor;
    }

    /**
     * Get the total number of items being paginated.
     *
     * @return int
     */
    public function nextCursor()
    {
        return $this->nextCursor;
    }

    /**
     * Determine if the list of items is empty or not.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return $this->items->isEmpty();
    }

    /**
     * Get the number of items for the current page.
     *
     * @return int
     */
    public function count()
    {
        return $this->items->count();
    }

    /**
     * Get the paginator's underlying collection.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCollection()
    {
        return $this->items;
    }

    /**
     * Set the paginator's underlying collection.
     *
     * @param  \Illuminate\Support\Collection  $collection
     * @return $this
     */
    public function setCollection(Collection $collection)
    {
        $this->items = $collection;

        return $this;
    }

    /**
     * Resolve the current cursor or return the default value.
     *
     * @param  string $cursorName
     * @param  int    $default
     * @return int
     */
    public static function resolveCursor($cursorName = 'cursor', $default = 1)
    {
        if (isset(static::$currentCursorResolver)) {
            return call_user_func(static::$currentCursorResolver, $cursorName);
        }

        return $default;
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
