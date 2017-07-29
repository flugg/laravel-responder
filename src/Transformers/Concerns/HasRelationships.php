<?php

namespace Flugg\Responder\Transformers\Concerns;

use Closure;

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
     * Indicates if the transformer has whitelisted all relations.
     *
     * @return bool
     */
    public function allRelationsAllowed(): bool
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
            return $relation !== '*';
        });
    }

    /**
     * Get a list of default relations.
     *
     * @return string[]
     */
    public function getDefaultRelations(): array
    {
        return collect($this->load)->keys()->mapWithKeys(function ($relation) {
            if (method_exists($this, $method = 'load' . ucfirst($relation))) {
                return [$relation => $this->makeEagerLoadCallback($method)];
            }

            return $relation;
        });
    }

    /**
     * Extract a deep list of default relations, recursively.
     *
     * @return string[]
     */
    public function extractDefaultRelations(): array
    {
        return collect($this->getDefaultRelations())->merge($this->load->map(function ($transformer, $relation) {
            return collect($this->resolveTransformer($transformer)->extractDefaultRelations())
                ->keys()
                ->map(function ($nestedRelation) use ($relation) {
                    return "$relation.$nestedRelation";
                });
        }))->all();
    }

    /**
     * Extract a deep list of default relations, recursively.
     *
     * @param  string $method
     * @return \Closure
     */
    public function makeEagerLoadCallback(string $method): Closure
    {
        return function ($query) use ($method) {
            return $this->$method($query);
        };
    }
}