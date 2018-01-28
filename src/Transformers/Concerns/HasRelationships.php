<?php

namespace Flugg\Responder\Transformers\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * A trait to be used by a transformer to handle relations
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
trait HasRelationships
{
    /**
     * List of available relations.
     *
     * @var string[]
     */
    protected $relations = [];

    /**
     * A list of autoloaded default relations.
     *
     * @var array
     */
    protected $load = [];

    /**
     * Get a list of whitelisted relations, including nested relations.
     *
     * @return array
     */
    public function whitelistedRelations(): array
    {
        $nestedRelations = $this->getNestedRelations($this->relations, function ($transformer) {
            return $transformer->whitelistedRelations();
        });

        return array_merge($this->normalizeRelations($this->relations), $nestedRelations);
    }

    /**
     * Get a list of default relations, including nested relations.
     *
     * @return array
     */
    public function defaultRelations(): array
    {
        $nestedRelations = $this->getNestedRelations($this->load, function ($transformer) {
            return $transformer->defaultRelations();
        });

        return array_merge($this->normalizeRelations($this->load), $nestedRelations);
    }

    /**
     * Extract a list of nested relations from the transformers provided in the
     * list of relations.
     *
     * @param  array    $relations
     * @param  callable $nestedCallback
     * @return array
     */
    protected function getNestedRelations(array $relations, callable $nestedCallback): array
    {
        return collect($relations)->filter(function ($transformer, $relation) {
            return ! is_numeric($relation) && ! is_null($transformer);
        })->map(function ($transformer) {
            return $this->resolveTransformer($transformer);
        })->flatMap(function ($transformer, $relation) use ($nestedCallback) {
            return collect($nestedRelations = $nestedCallback($transformer))
                ->keys()
                ->reduce(function ($value, $nestedRelation) use ($relation, $nestedRelations) {
                    return array_merge($value, ["$relation.$nestedRelation" => $nestedRelations[$nestedRelation]]);
                }, []);
        })->all();
    }

    /**
     * Normalize relations to force a key value structure.
     *
     * @param  array $relations
     * @return array
     */
    protected function normalizeRelations(array $relations): array
    {
        return collect(array_keys($relations))->reduce(function ($normalized, $relation) use ($relations) {
            if (is_numeric($relation)) {
                $relation = $relations[$relation];
            }

            return array_merge($normalized, [$relation => $this->getQueryConstraint($relation)]);
        }, []);
    }

    /**
     * Normalize relations to force a key value structure.
     *
     * @param  string $relation
     * @return \Closure|null
     */
    protected function getQueryConstraint(string $relation)
    {
        if (! method_exists($this, $method = 'load' . ucfirst($relation))) {
            return null;
        }

        return function ($query) use ($method) {
            return $this->$method($query);
        };
    }

    /**
     * Resolve a relationship from a model instance.
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @param  string                              $identifier
     * @return mixed
     */
    protected function resolveRelation(Model $model, string $identifier)
    {
        $relation = $model->$identifier;

        if (method_exists($this, $method = 'filter' . ucfirst($identifier))) {
            return $this->$method($relation);
        }

        return $relation;
    }

    /**
     * Get a related transformer class mapped to a relation identifier.
     *
     * @param  string $identifier
     * @return string|null
     */
    protected function getRelatedTransformerName(string $identifier)
    {
        $relations = array_merge($this->relations, $this->load);

        return array_has($relations, $identifier) ? $relations[$identifier] : null;
    }

    /**
     * Resolve a transformer from a class name string.
     *
     * @param  string $transformer
     * @return mixed
     */
    protected abstract function resolveTransformer(string $transformer);
}