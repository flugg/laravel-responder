<?php

namespace Flugg\Responder\Transformers\Concerns;

use Flugg\Responder\Contracts\Transformers\TransformerResolver;
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
     * Get a list of default relations with eager load constraints.
     *
     * @return array
     */
    public function defaultRelations(): array
    {
        $this->load = $this->normalizeRelations($this->load);

        $relations = $this->addEagerLoadConstraints(array_keys($this->load));

        return array_merge($relations, $this->getNestedDefaultRelations());
    }

    /**
     * Normalize relations to force a key value structure.
     *
     * @param  array $relations
     * @return array
     */
    protected function normalizeRelations(array $relations): array
    {
        $normalized = [];

        foreach ($relations as $relation => $transformer) {
            if (is_numeric($relation)) {
                $relation = $transformer;
                $transformer = null;
            }

            $normalized[$relation] = $transformer;
        }

        return $normalized;
    }

    /**
     * Add eager load constraints to a list of relations.
     *
     * @param  array $relations
     * @return array
     */
    protected function addEagerLoadConstraints(array $relations): array
    {
        $eagerLoads = [];

        foreach ($relations as $relation) {
            if (method_exists($this, $method = 'load' . ucfirst($relation))) {
                $eagerLoads[$relation] = function ($query) use ($method) {
                    return $this->$method($query);
                };
            } else {
                $eagerLoads[] = $relation;
            }
        }

        return $eagerLoads;
    }

    /**
     * Get a list of nested default relationships with eager load constraints.
     *
     * @return array
     */
    protected function getNestedDefaultRelations(): array
    {
        return Collection::make($this->load)->filter(function ($transformer) {
            return ! is_null($transformer);
        })->flatMap(function ($transformer, $relation) {
            return array_map(function ($nestedRelation) use ($relation) {
                return "$relation.$nestedRelation";
            }, $this->resolveTransformer($transformer)->defaultRelations());
        })->all();
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
        if (method_exists($this, $method = 'filter' . ucfirst($identifier))) {
            return $this->$method($model->$identifier);
        }

        return $model->$identifier;
    }

    /**
     * Resolve a related transformer from a class name string.
     *
     * @param  string $transformer
     * @return mixed
     */
    protected function resolveTransformer(string $transformer)
    {
        $resolver = $this->resolveContainer()->make(TransformerResolver::class);

        return $resolver->resolve($transformer);
    }

    /**
     * Resolve a container using the resolver callback.
     *
     * @return \Illuminate\Contracts\Container\Container
     */
    protected abstract function resolveContainer(): Container;
}