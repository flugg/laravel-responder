<?php

namespace Flugg\Responder\Contracts\Http;

use Flugg\Responder\Http\SuccessResponse;

/**
 * Class for normalizing query builder to a success response.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class QueryBuilderNormalizer
{
    /**
     * Normalize response data.
     *
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder $data
     * @return \Flugg\Responder\Http\SuccessResponse
     */
    public function normalize($data): SuccessResponse
    {
        return (new SuccessResponse())->setData($data->get()->toArray());
    }
}
