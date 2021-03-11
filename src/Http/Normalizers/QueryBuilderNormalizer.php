<?php

namespace Flugg\Responder\Http\Normalizers;

use Flugg\Responder\Contracts\Http\Normalizer;
use Flugg\Responder\Http\Resources\Item;
use Flugg\Responder\Http\SuccessResponse;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

/**
 * Class for normalizing query builders to success responses.
 */
class QueryBuilderNormalizer extends EloquentNormalizer implements Normalizer
{
    /**
     * The data being normalized.
     *
     * @var \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    protected $data;

    /**
     * Create a new response normalizer instance.
     *
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $data
     */
    public function __construct($data)
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
        $resource = $this->data instanceof EloquentBuilder
            ? $this->buildCollection($this->data->get())
            : new Item($this->data->get()->toArray());

        return new SuccessResponse($resource);
    }
}
