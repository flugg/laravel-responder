<?php

namespace Flugg\Responder\Http\Normalizers;

use Flugg\Responder\Contracts\Http\Normalizer;
use Flugg\Responder\Http\Resource;
use Flugg\Responder\Http\SuccessResponse;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Class for normalizing Eloquent relation query builders to success responses.
 */
class RelationNormalizer implements Normalizer
{
    /**
     * Normalize response data.
     *
     * @param \Illuminate\Database\Eloquent\Relations\Relation $data
     * @return \Flugg\Responder\Http\SuccessResponse
     */
    public function normalize($data): SuccessResponse
    {
        return (new SuccessResponse())->setResource(new Resource($this->makeArrayFromRelation($data)));
    }

    /**
     * Make an array from the Eloquent relation query builder.
     *
     * @param \Illuminate\Database\Eloquent\Relations\Relation $data
     * @return array
     */
    protected function makeArrayFromRelation(Relation $data): array
    {
        foreach ([BelongsTo::class, HasOne::class, MorphOne::class, MorphTo::class] as $class) {
            if ($data instanceof $class) {
                return $data->first()->toArray();
            }
        }

        return $data->get()->toArray();
    }
}
