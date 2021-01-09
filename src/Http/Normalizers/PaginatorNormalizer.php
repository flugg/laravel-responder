<?php

namespace Flugg\Responder\Http\Normalizers;

use Flugg\Responder\Adapters\IlluminatePaginatorAdapter;
use Flugg\Responder\Contracts\Http\Normalizer;
use Flugg\Responder\Http\SuccessResponse;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Class for normalizing Illuminate paginators to success responses.
 */
class PaginatorNormalizer extends EloquentNormalizer implements Normalizer
{
    /**
     * The data being normalized.
     *
     * @var \Illuminate\Pagination\LengthAwarePaginator
     */
    protected $data;

    /**
     * Create a new response normalizer instance.
     *
     * @param \Illuminate\Pagination\LengthAwarePaginator $data
     */
    public function __construct(LengthAwarePaginator $data)
    {
        $this->data = $data;
    }

    /**
     * Normalize response data.
     *
     * @return \Flugg\Responder\Http\SuccessResponse
     */
    public function normalize(): SuccessResponse
    {
        return (new SuccessResponse)
            ->setResource($this->buildCollection($this->data->getCollection()))
            ->setPaginator(new IlluminatePaginatorAdapter($this->data));
    }
}
