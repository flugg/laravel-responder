<?php

namespace Flugg\Responder\Http\Normalizers;

use Flugg\Responder\Contracts\Http\Normalizer;
use Flugg\Responder\Http\Resources\Item;
use Flugg\Responder\Http\SuccessResponse;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Class for normalizing query builders to success responses.
 */
class ArrayableNormalizer implements Normalizer
{
    /**
     * The data being normalized.
     *
     * @var \Illuminate\Contracts\Support\Arrayable
     */
    protected $data;

    /**
     * Create a new response normalizer instance.
     *
     * @param \Illuminate\Contracts\Support\Arrayable $data
     */
    public function __construct(Arrayable $data)
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
        return (new SuccessResponse(new Item($this->data->toArray())));
    }
}
