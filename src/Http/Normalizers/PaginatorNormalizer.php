<?php

namespace Flugg\Responder\Http\Normalizers;

use Flugg\Responder\Adapters\IlluminatePaginatorAdapter;
use Flugg\Responder\Contracts\Http\Normalizer;
use Flugg\Responder\Http\Resource;
use Flugg\Responder\Http\SuccessResponse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use InvalidArgumentException;

/**
 * Class for normalizing Illuminate paginators to success responses.
 */
class PaginatorNormalizer implements Normalizer
{
    /**
     * Normalize response data.
     *
     * @param \Illuminate\Contracts\Pagination\LengthAwarePaginator $data
     * @return \Flugg\Responder\Http\SuccessResponse
     * @throws \InvalidArgumentException
     */
    public function normalize($data): SuccessResponse
    {
        if (!$data instanceof LengthAwarePaginator) {
            throw new InvalidArgumentException('Data must be instance of ' . LengthAwarePaginator::class);
        }

        return (new SuccessResponse())
            ->setResource(new Resource($data->items()))
            ->setPaginator(new IlluminatePaginatorAdapter($data));
    }
}
