<?php

namespace Flugg\Responder\Contracts\Http;

use Flugg\Responder\Http\SuccessResponse;

/**
 * Class for normalizing API resource to a success response.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class RelationNormalizer
{
    /**
     * Normalize response data.
     *
     * @param \Illuminate\Http\Resources\Json\JsonResource $data
     * @return \Flugg\Responder\Http\SuccessResponse
     */
    public function normalize($data): SuccessResponse
    {
        $response = $data->response();

        return (new SuccessResponse())
            ->setData($data->resolve())
            ->setStatus($response->status())
            ->setHeaders($response->headers->all())
            ->setMeta(array_merge_recursive($data->with(app('request')), $data->additional));
    }
}
