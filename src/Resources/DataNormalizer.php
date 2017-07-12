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
use Illuminate\Support\Collection;

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
     *
     *
     * @param  mixed $data
     * @return mixed
     */
    public function normalize($data = null)
    {
        if (is_array($data)) {
            return new Collection($data);
        }

        foreach ($this->getMethodMappings() as $method => $types) {
            if (in_array(get_class($data), $types)) {
                return $this->$method($data);
            }
        }

        return $data;
    }

    /**
     *
     *
     * @return array
     */
    protected function getMethodMappings(): array
    {
        return [
            'normalizeBuilder' => [Builder::class],
            'normalizePaginator' => [Paginator::class, CursorPaginator::class],
            'normalizeRelation' => [Relation::class],
        ];
    }

    /**
     *
     *
     * @param  \Illuminate\Database\Query\Builder $builder
     * @return \Illuminate\Support\Collection
     */
    protected function normalizeBuilder(Builder $builder): Collection
    {
        return $builder->get();
    }

    /**
     *
     *
     * @param  \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Flugg\Responder\Pagination\CursorPaginator $paginator
     * @return \Illuminate\Support\Collection
     */
    protected function normalizePaginator($paginator): Collection
    {
        return $paginator->getCollection();
    }

    /**
     *
     *
     * @param  \Illuminate\Database\Eloquent\Relations\Relation $relation
     * @return \Illuminate\Support\Collection
     */
    protected function normalizeRelation(Relation $relation): Collection
    {
        $single = in_array(get_class($relation), [BelongsTo::class, HasOne::class, MorphOne::class, MorphTo::class]);

        return $single ? $relation->first() : $relation->get();
    }
}