<?php

namespace Flugg\Responder\Http\Normalizers;

use Flugg\Responder\Contracts\Http\Normalizer;
use Flugg\Responder\Http\Resources\Item;
use Flugg\Responder\Http\SuccessResponse;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

/**
 * Class for normalizing Eloquent collections to success responses.
 */
class CollectionNormalizer extends EloquentNormalizer implements Normalizer
{
    /**
     * The data being normalized.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $data;

    /**
     * Create a new response normalizer instance.
     *
     * @param \Illuminate\Support\Collection $data
     */
    public function __construct(Collection $data)
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
        $resource = $this->data instanceof EloquentCollection ? $this->buildCollection($this->data)
            : new Item($this->data->toArray());

        return new SuccessResponse($resource);
    }
}
