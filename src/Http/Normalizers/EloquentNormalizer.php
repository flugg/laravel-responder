<?php

namespace Flugg\Responder\Http\Normalizers;

use Flugg\Responder\Contracts\Http\Normalizer;
use Flugg\Responder\Http\Resources\Collection;
use Flugg\Responder\Http\Resources\Item;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;

/**
 * Abstract class for normalizing Eloquent classes to success responses.
 */
abstract class EloquentNormalizer implements Normalizer
{
    /**
     * Build a resource object from an Eloquent model.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return \Flugg\Responder\Http\Resources\Item
     */
    protected function buildResource(Model $model): Item
    {
        $resourceKey = $this->resolveResourceKey($model);
        $relations = $this->extractRelations($model);

        return new Item($model->withoutRelations()->toArray(), $resourceKey, $relations);
    }

    /**
     * Build a collection of resources from an Eloquent collection.
     *
     * @param \Illuminate\Database\Eloquent\Collection $collection
     * @return \Flugg\Responder\Http\Resources\Collection
     */
    protected function buildCollection(EloquentCollection $collection): Collection
    {
        $resourceKey = ! $collection->isEmpty() ? $this->resolveResourceKey($collection->first()) : null;

        return new Collection(array_map([$this, 'buildResource'], $collection->all()), $resourceKey);
    }

    /**
     * Resolve a resource key from the Eloquent model.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return string
     */
    protected function resolveResourceKey(Model $model): string
    {
        if (method_exists($model, 'getResourceKey')) {
            return $model->getResourceKey();
        }

        return $model->getTable();
    }

    /**
     * Extract relations from an Eloquent model.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return array
     */
    protected function extractRelations(Model $model): array
    {
        return array_map(function ($relation) {
            return $relation instanceof Model ? $this->buildResource($relation) : $this->buildCollection($relation);
        }, $model->getRelations());
    }
}
