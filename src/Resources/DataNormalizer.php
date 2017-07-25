<?php

namespace Flugg\Responder\Resources;

use Flugg\Responder\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;

/**
 * This class is responsible for normalizing resource data.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class DataNormalizer
{
    /**
     * Normalize the data for a resource.
     *
     * @param  mixed $data
     * @return mixed
     */
    public function normalize($data = null)
    {
        if ($data instanceof Builder || $data instanceof CursorPaginator) {
            return $data->get();
        } elseif ($data instanceof Paginator) {
            return $data->getCollection();
        } elseif ($data instanceof Relation) {
            return $this->normalizeRelation($data);
        }

        return $data;
    }

    /**
     * Normalize a relationship.
     *
     * @param  \Illuminate\Database\Eloquent\Relations\Relation $relation
     * @return \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Model|null
     */
    protected function normalizeRelation(Relation $relation)
    {
        return $this->isSingularRelation($relation) ? $relation->first() : $relation->get();
    }

    /**
     * Indicates if a relationship is singular.
     *
     * @param  \Illuminate\Database\Eloquent\Relations\Relation $relation
     * @return bool
     */
    protected function isSingularRelation(Relation $relation): bool
    {
        $singularRelations = [BelongsTo::class, HasOne::class, MorphOne::class, MorphTo::class];

        foreach ($singularRelations as $singularRelation) {
            if ($relation instanceof $singularRelation) {
                return true;
            }
        }

        return false;
    }
}