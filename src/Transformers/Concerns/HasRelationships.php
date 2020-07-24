<?php

namespace Flugg\Responder\Transformers\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

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
     * Get a list of whitelisted relations that are requested, including nested relations.
     *
     * @param  array $requested
     * @return array
     */
    public function relations(array $requested = []): array
    {
        $requested = $this->normalizeRelations($requested);
        $relations = $this->applyQueryConstraints($this->extractRelations($requested));
        $nestedRelations = $this->nestedRelations($requested, $relations, 'relations');

        return array_merge($relations, $nestedRelations);
    }

    /**
     * Get a list of default relations including nested relations.
     *
     * @param  array $requested
     * @return array
     */
    public function defaultRelations(array $requested = []): array
    {
        $requested = $this->normalizeRelations($requested);
        $relations = $this->applyQueryConstraints($this->normalizeRelations($this->load));
        $nestedRelations = $this->nestedRelations($relations, array_merge($relations, $requested), 'defaultRelations');

        return array_merge($relations, $nestedRelations);
    }

    /**
     * Get a list of available relations from the transformer with a normalized structure.
     *
     * @return array
     */
    protected function availableRelations(): array
    {
        return $this->normalizeRelations(array_merge($this->relations, $this->load));
    }

    /**
     * Get nested relations from transformers resolved from the $available parameter that
     * also occur in the $requested parameter.
     *
     * @param  array  $requested
     * @param  array  $available
     * @param  string $method
     * @return array
     */
    protected function nestedRelations(array $requested, array $available, string $method): array
    {
        $transformers = $this->mappedTransformers($available);

        return collect(array_keys($transformers))->reduce(function ($nestedRelations, $relation) use ($requested, $method, $transformers) {
            $transformer = $transformers[$relation];
            $children = $this->extractChildRelations($requested, $relation);
            $childRelations = $this->wrapChildRelations($transformer->$method($children), $relation);

            return array_merge($nestedRelations, $childRelations);
        }, []);
    }

    /**
     * Extract available root relations from the given list of relations.
     *
     * @param  array $relations
     * @return array
     */
    protected function extractRelations(array $relations): array
    {
        $available = $this->availableRelations();

        return array_filter($this->mapRelations($relations, function ($relation, $constraint) {
            $identifier = explode('.', $relation)[0];
            $constraint = $identifier === $relation ? $constraint : null;

            return [$identifier => $constraint ?: $this->resolveQueryConstraint($identifier)];
        }), function ($relation) use ($available) {
            return Arr::has($available, explode(':', $relation)[0]);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Extract all nested relations under a given identifier.
     *
     * @param  array  $relations
     * @param  string $identifier
     * @return array
     */
    protected function extractChildRelations(array $relations, string $identifier): array
    {
        return array_reduce(array_keys($relations), function ($nested, $relation) use ($relations, $identifier) {
            if (! Str::startsWith($relation, "$identifier.")) {
                return $nested;
            }

            $nestedIdentifier = explode('.', $relation);
            array_shift($nestedIdentifier);

            return array_merge($nested, [implode('.', $nestedIdentifier) => $relations[$relation]]);
        }, []);
    }

    /**
     * Wrap the identifier of each relation of the given list of nested relations with
     * the parent relation identifier using dot notation.
     *
     * @param  array  $nestedRelations
     * @param  string $relation
     * @return array
     */
    protected function wrapChildRelations(array $nestedRelations, string $relation): array
    {
        return $this->mapRelations($nestedRelations, function ($nestedRelation, $constraint) use ($relation) {
            return ["$relation.$nestedRelation" => $constraint];
        });
    }

    /**
     * Normalize relations to force an [identifier => constraint/transformer] structure.
     *
     * @param  array $relations
     * @return array
     */
    protected function normalizeRelations(array $relations): array
    {
        return array_reduce(array_keys($relations), function ($normalized, $relation) use ($relations) {
            if (is_numeric($relation)) {
                return array_merge($normalized, [$relations[$relation] => null]);
            }

            return array_merge($normalized, [$relation => $relations[$relation]]);
        }, []);
    }

    /**
     * Map over a list of relations with the [identifier => constraint/transformer] structure.
     *
     * @param  array    $relations
     * @param  callable $callback
     * @return array
     */
    protected function mapRelations(array $relations, callable $callback): array
    {
        $mapped = [];

        foreach ($relations as $identifier => $value) {
            $mapped = array_merge($mapped, $callback($identifier, $value));
        }

        return $mapped;
    }

    /**
     * Applies any query constraints defined in the transformer to the list of relaations.
     *
     * @param  array $relations
     * @return array
     */
    protected function applyQueryConstraints(array $relations): array
    {
        return $this->mapRelations($relations, function ($relation, $constraint) {
            return [$relation => is_callable($constraint) ? $constraint : $this->resolveQueryConstraint($relation)];
        });
    }

    /**
     * Resolve a query constraint for a given relation identifier.
     *
     * @param  string $identifier
     * @return \Closure|null
     */
    protected function resolveQueryConstraint(string $identifier)
    {
        if(config('responder.use_camel_case_relations')) {
            $identifier = Str::camel($identifier);
        }

        if (! method_exists($this, $method = 'load' . ucfirst($identifier))) {
            return null;
        }

        return function ($query) use ($method) {
            return $this->$method($query);
        };
    }

    /**
     * Resolve a relation from a model instance and an identifier.
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @param  string                              $identifier
     * @return mixed
     */
    protected function resolveRelation(Model $model, string $identifier)
    {
        if(config('responder.use_camel_case_relations')) {
            $identifier = Str::camel($identifier);
        }

        $relation = $model->$identifier;

        if (method_exists($this, $method = 'filter' . ucfirst($identifier))) {
            return $this->$method($relation);
        }

        return $relation;
    }

    /**
     * Resolve a list of transformers from a list of relations mapped to transformers.
     *
     * @param  array $relations
     * @return array
     */
    protected function mappedTransformers(array $relations): array
    {
        $transformers = collect($this->availableRelations())->filter(function ($transformer) {
            return ! is_null($transformer);
        })->map(function ($transformer) {
            return $this->resolveTransformer($transformer);
        })->all();

        return array_intersect_key($transformers, $relations);
    }

    /**
     * Get a related transformer class mapped to a relation identifier.
     *
     * @param  string $identifier
     * @return string|null
     */
    protected function mappedTransformerClass(string $identifier)
    {
        return $this->availableRelations()[$identifier] ?? null;
    }

    /**
     * Resolve a transformer from a class name string.
     *
     * @param  string $transformer
     * @return mixed
     */
    protected abstract function resolveTransformer(string $transformer);
}