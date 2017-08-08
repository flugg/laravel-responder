<?php

namespace Flugg\Responder\Resources;

use Flugg\Responder\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
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
        if ($this->isInstanceOf($data, [Builder::class, EloquentBuilder::class, CursorPaginator::class])) {
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
        if ($this->isInstanceOf($relation, [BelongsTo::class, HasOne::class, MorphOne::class, MorphTo::class])) {
            return $relation->first();
        }

        return $relation->get();
    }

    /**
     * Indicates if the given data is an instance of any of the given class names.
     *
     * @param  mixed $data
     * @param  array $classes
     * @return bool
     */
    protected function isInstanceOf($data, array $classes): bool
    {
        foreach ($classes as $class) {
            if ($data instanceof $class) {
                return true;
            }
        }

        return false;
    }
}