<?php

namespace Flugg\Responder\Transformers\Concerns;

use Closure;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

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
    protected $relations = ['*'];

    /**
     * A list of autoloaded default relations.
     *
     * @var array
     */
    protected $load = [];

    /**
     * Indicates if all relations are allowed.
     *
     * @return bool
     */
    public function allowsAllRelations(): bool
    {
        return $this->relations == ['*'];
    }

    /**
     * Get a list of whitelisted relations.
     *
     * @return string[]
     */
    public function getRelations(): array
    {
        return array_filter($this->relations, function ($relation) {
            return $relation != '*';
        });
    }

    /**
     * Get a list of default relations.
     *
     * @return string[]
     */
    public function getDefaultRelations(): array
    {
        return array_keys($this->load);
    }

    /**
     * Extract a deep list of default relations, recursively.
     *
     * @return string[]
     */
    public function extractDefaultRelations(): array
    {
        return collect($this->getDefaultRelationsWithEagerLoads())
            ->merge(collect($this->load)->map(function ($transformer, $relation) {
                return collect($this->resolveContainer()->make($transformer)->extractDefaultRelations())
                    ->keys()
                    ->map(function ($nestedRelation) use ($relation) {
                        return "$relation.$nestedRelation";
                    });
            }))
            ->all();
    }

    /**
     *
     *
     * @return string[]
     */
    protected function getDefaultRelationsWithEagerLoads(): array
    {
        return collect($this->load)->keys()->mapWithKeys(function ($relation) {
            if (method_exists($this, $method = 'load' . ucfirst($relation))) {
                return [$relation => $this->makeEagerLoadCallback($method)];
            }

            return [$relation => function () { }];
        })->all();
    }

    /**
     *
     *
     * @param  string $method
     * @return \Closure
     */
    protected function makeEagerLoadCallback(string $method): Closure
    {
        return function ($query) use ($method) {
            return $this->$method($query);
        };
    }

    /**
     *
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param  string                             $identifier
     * @return mixed
     */
    protected function resolveRelation(Model $model, string $identifier)
    {
        if (method_exists($this, $method = 'filter' . ucfirst($identifier))) {
            return $this->$method($model->$identifier);
        }

        return $model->$identifier;
    }

    /**
     * Resolve a container using the resolver callback.
     *
     * @return \Illuminate\Contracts\Container\Container
     */
    protected abstract function resolveContainer(): Container;
}