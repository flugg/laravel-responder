<?php

namespace Flugg\Responder\Http\Normalizers;

use Flugg\Responder\Adapters\IlluminatePaginatorAdapter;
use Flugg\Responder\Contracts\Http\Normalizer;
use Flugg\Responder\Http\SuccessResponse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class for normalizing Illuminate paginators to success responses.
 */
class PaginatorNormalizer extends EloquentNormalizer implements Normalizer
{
    /**
     * The data being normalized.
     *
     * @var \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    protected $data;

    /**
     * Create a new response normalizer instance.
     *
     * @param \Illuminate\Contracts\Pagination\LengthAwarePaginator $data
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
        return (new SuccessResponse($this->buildCollection(Collection::make($this->data->items()))))
            ->setPaginator(new IlluminatePaginatorAdapter($this->data));
    }
}
