<?php

namespace Flugg\Responder\Contracts\Http;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * A contract for building success responses.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
interface SuccessResponseBuilder extends ResponseBuilder
{
    /**
     * Make a success response from the given data.
     *
     * @param array|Arrayable|Builder|JsonResource|QueryBuilder|Relation $data
     * @return $this
     */
    public function data($data = []);
}
