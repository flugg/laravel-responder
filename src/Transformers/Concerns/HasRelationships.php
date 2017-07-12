<?php

namespace Flugg\Responder\Transformers\Concerns;

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
        return array_keys($this->load);
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
}