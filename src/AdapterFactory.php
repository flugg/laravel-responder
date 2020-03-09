<?php

namespace Flugg\Responder;

use Flugg\Responder\Contracts\AdapterFactory as AdapterFactoryContract;
use Flugg\Responder\Contracts\Pagination\CursorPaginator;
use Flugg\Responder\Contracts\Pagination\Paginator;
use Flugg\Responder\Contracts\Validation\Validator;

/**
 * A factory class for creating adapters.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class AdapterFactory implements AdapterFactoryContract
{
    /**
     * Map of adapters.
     *
     * @var array
     */
    protected $adapters = [];

    /**
     * Create a new adapter factory instance.
     *
     * @param array $adapters
     */
    public function __construct(array $adapters = [])
    {
        $this->adapters = $adapters;
    }

    /**
     * Make a paginator adapter if a mapping exists.
     *
     * @param mixed $instance
     * @return Paginator|null
     */
    public function makePaginator($instance): ?Paginator
    {
        return $this->make(Paginator::class, $instance);
    }

    /**
     * Make a cursor paginator adapter if a mapping exists.
     *
     * @param mixed $instance
     * @return CursorPaginator|null
     */
    public function makeCursorPaginator($instance): ?CursorPaginator
    {
        return $this->make(CursorPaginator::class, $instance);
    }

    /**
     * Make a validator adapter if a mapping exists.
     *
     * @param mixed $instance
     * @return Validator|null
     */
    public function makeValidator($instance): ?Validator
    {
        return $this->make(Validator::class, $instance);
    }

    /**
     * Make an adapter if a mapping exists.
     *
     * @param string $type
     * @param mixed $instance
     * @return object|void
     */
    protected function make(string $type, $instance)
    {
        foreach (($this->adapters[$type] ?? []) as $class => $adapter) {
            if ($instance instanceof $class) {
                return new $adapter($instance);
            }
        }
    }
}
