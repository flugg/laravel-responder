<?php

namespace Flugg\Responder\Http\Normalizers;

use Flugg\Responder\Contracts\Http\Normalizer;
use Flugg\Responder\Http\Resource;
use Flugg\Responder\Http\SuccessResponse;
use Illuminate\Contracts\Support\Arrayable;
use InvalidArgumentException;

/**
 * Class for normalizing query builders to success responses.
 */
class ArrayableNormalizer implements Normalizer
{
    /**
     * Normalize response data.
     *
     * @param \Illuminate\Contracts\Support\Arrayable $data
     * @return \Flugg\Responder\Http\SuccessResponse
     * @throws \InvalidArgumentException
     */
    public function normalize($data): SuccessResponse
    {
        if (!$data instanceof Arrayable) {
            throw new InvalidArgumentException('Data must be instance of ' . Arrayable::class);
        }

        return (new SuccessResponse())->setResource(new Resource($data->toArray()));
    }
}
