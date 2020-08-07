<?php

namespace Flugg\Responder\Http\Normalizers;

use Flugg\Responder\Contracts\Http\Normalizer;
use Flugg\Responder\Http\Resource;
use Flugg\Responder\Http\SuccessResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\MissingValue;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * Class for normalizing API resources to success responses.
 */
class ResourceNormalizer implements Normalizer
{
    /**
     * A request object.
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * Create a new normalizer instance.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Normalize response data.
     *
     * @param \Illuminate\Http\Resources\Json\JsonResource $data
     * @return \Flugg\Responder\Http\SuccessResponse
     */
    public function normalize($data): SuccessResponse
    {
        if (!$data instanceof JsonResource) {
            throw new InvalidArgumentException('Data must be instance of ' . JsonResource::class);
        }

        $response = $data->response();

        return (new SuccessResponse())
            ->setResource($this->buildResource($data))
            ->setStatus($response->status())
            ->setHeaders($response->headers->all())
            ->setMeta(array_merge_recursive($data->with($this->request), $data->additional));
    }

    /** Build a resource object from the Eloquent API resource. */
    protected function buildResource(JsonResource $data): Resource
    {
        return tap(new Resource($data->resolve()), function ($resource) use ($data) {
            Collection::make($data->toArray($this->request))->map(function ($value, $key) use ($resource) {
                if ($value instanceof JsonResource && !$value->resource instanceof MissingValue) {
                    $resource->setData(Arr::except($resource->data(), $key));
                    $resource->addRelation($this->buildResource($value));
                }
            });
        });
    }
}
