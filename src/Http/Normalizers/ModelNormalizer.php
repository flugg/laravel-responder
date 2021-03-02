<?php

namespace Flugg\Responder\Http\Normalizers;

use Flugg\Responder\Contracts\Http\Normalizer;
use Flugg\Responder\Http\SuccessResponse;
use Illuminate\Database\Eloquent\Model;

/**
 * Class for normalizing Eloquent models to success responses.
 */
class ModelNormalizer extends EloquentNormalizer implements Normalizer
{
    /**
     * The data being normalized.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $data;

    /**
     * Create a new response normalizer instance.
     *
     * @param \Illuminate\Database\Eloquent\Model $data
     */
    public function __construct(Model $data)
    {
        $this->data = $data;
    }

    /**
     * Normalize response data.
     *
     * @throws \Flugg\Responder\Exceptions\InvalidStatusCodeException
     * @return \Flugg\Responder\Http\SuccessResponse
     */
    public function normalize(): SuccessResponse
    {
        return (new SuccessResponse($this->buildResource($this->data)))
            ->setStatus($this->data->wasRecentlyCreated ? 201 : 200);
    }
}
