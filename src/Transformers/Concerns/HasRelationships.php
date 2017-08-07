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
    public function getDefaultRelations(): array
    {
        $this->load = Collection::make($this->load)->mapWithKeys(function ($transformer, $relation) {
            return is_numeric($relation) ? [$transformer => null] : [$relation => $transformer];
        })->all();

        return array_merge($this->getScopedDefaultRelations(), $this->getNestedDefaultRelations());
    }

    /**
     * Get a list of scoped default relationships with eager load constraints.
     *
     * @return array
     */
    public function getScopedDefaultRelations(): array
    {
        $relations = [];

        foreach (array_keys($this->load) as $relation) {
            if (method_exists($this, $method = 'load' . ucfirst($relation))) {
                $relations[$relation] = function ($query) use ($method) {
                    return $this->$method($query);
                };
            } else {
                $relations[] = $relation;
            }
        }

        return $relations;
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
            }, $this->resolveTransformer($transformer)->getDefaultRelations());
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