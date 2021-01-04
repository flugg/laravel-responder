<?php

namespace Flugg\Responder\Http\Normalizers;

use Flugg\Responder\Contracts\Http\Normalizer;
use Flugg\Responder\Http\SuccessResponse;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Class for normalizing Eloquent relations to success responses.
 */
class RelationNormalizer extends EloquentNormalizer implements Normalizer
{
    /**
     * The data being normalized.
     *
     * @var \Illuminate\Database\Eloquent\Relations\Relation
     */
    protected $data;

    /**
     * Create a new response normalizer instance.
     *
     * @param \Illuminate\Database\Eloquent\Relations\Relation $data
     */
    public function __construct(Relation $data)
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
        $resource = $this->isOneToOneRelation($this->data)
            ? $this->buildResource($this->data->first())
            : $this->buildCollection($this->data->get());

        return (new SuccessResponse())->setResource($resource);
    }

    /**
     * Check if the relationship is a one-to-one relation.
     *
     * @param \Illuminate\Database\Eloquent\Relations\Relation $relation
     * @return bool
     */
    protected function isOneToOneRelation(Relation $relation): bool
    {
        foreach ([BelongsTo::class, HasOne::class, MorphOne::class, MorphTo::class] as $class) {
            if ($relation instanceof $class) {
                return true;
            }
        }

        return false;
    }
}
