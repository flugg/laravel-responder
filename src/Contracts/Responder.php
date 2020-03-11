<?php

namespace Flugg\Responder\Contracts;

use Exception;
use Flugg\Responder\Http\Builders\ErrorResponseBuilder;
use Flugg\Responder\Http\Builders\SuccessResponseBuilder;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * Contract for building success- and error responses.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
interface Responder
{
    /**
     * Build a success response.
     *
     * @param array|Arrayable|Builder|QueryBuilder|Relation $data
     * @return SuccessResponseBuilder
     */
    public function success($data = []): SuccessResponseBuilder;

    /**
     * Build an error response.
     *
     * @param int|string|Exception|null $code
     * @param string|Exception|null $message
     * @return ErrorResponseBuilder
     */
    public function error($code = null, $message = null): ErrorResponseBuilder;
}
