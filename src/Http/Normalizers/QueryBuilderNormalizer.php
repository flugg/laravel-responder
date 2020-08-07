<?php

namespace Flugg\Responder\Http\Normalizers;

use Flugg\Responder\Contracts\Http\Normalizer;
use Flugg\Responder\Http\Resource;
use Flugg\Responder\Http\SuccessResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use InvalidArgumentException;

/**
 * Class for normalizing query builders to success responses.
 */
class QueryBuilderNormalizer implements Normalizer
{
    /**
     * Normalize response data.
     *
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $data
     * @return \Flugg\Responder\Http\SuccessResponse
     * @throws \InvalidArgumentException
     */
    public function normalize($data): SuccessResponse
    {
        if (!$data instanceof QueryBuilder && !$data instanceof Builder) {
            throw new InvalidArgumentException('Data must be instance of ' . QueryBuilder::class . ' or ' . Builder::class);
        }

        return (new SuccessResponse())->setResource(new Resource($data->get()->toArray()));
    }
}
