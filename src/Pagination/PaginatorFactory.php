<?php

namespace Flugg\Responder\Pagination;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Pagination\PaginatorInterface;

/**
 * A factory class for making Fractal paginator adapters from a Laravel paginator.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class PaginatorFactory
{
    /**
     * A list of query string values appended to the paginator links.
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
     * Make a Fractal paginator adapter from a Laravel paginator.
     *
     * @param  \Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator
     * @return \League\Fractal\Pagination\PaginatorInterface
     */
    public function make(LengthAwarePaginator $paginator): PaginatorInterface
    {
        $paginator->appends($this->parameters);

        return new IlluminatePaginatorAdapter($paginator);
    }
}
