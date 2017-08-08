<?php

namespace Flugg\Responder\Transformers\Concerns;

use Illuminate\Support\Collection;
use League\Fractal\Resource\ResourceInterface;
use League\Fractal\Scope;

/**
 * A trait to be used by a transformer to override Fractal's transformer methods.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
trait OverridesFractal
{
    /**
     * Overrides Fractal's getter for available includes.
     *
     * @return array
     */
    public function getAvailableIncludes()
    {
        if (! isset($this->relations)) {
            return [];
        } elseif ($this->relations == ['*']) {
            return $this->resolveScopedIncludes($this->getCurrentScope());
        }

        return array_filter($this->relations, function ($relation) {
            return $relation != '*';
        });
    }

    /**
     * Overrides Fractal's getter for default includes.
     *
     * @return array
     */
    public function getDefaultIncludes()
    {
        return Collection::make($this->load)->map(function ($transformer, $relation) {
            return is_numeric($relation) ? $transformer : $relation;
        })->all();
    }

    /**
     * Overrides Fractal's method for including a relation.
     *
     * @param  \League\Fractal\Scope $scope
     * @param  string                $identifier
     * @param  mixed                 $data
     * @return \League\Fractal\Resource\ResourceInterface
     */
    protected function callIncludeMethod(Scope $scope, $identifier, $data)
    {
        $parameters = $this->resolveScopedParameters($scope, $identifier);

        return $this->includeResource($identifier, $data, $parameters);
    }

    /**
     * Resolve scoped includes for the given scope.
     *
     * @param  \League\Fractal\Scope $scope
     * @return array
     */
    protected function resolveScopedIncludes(Scope $scope): array
    {
        $level = count($scope->getParentScopes());
        $includes = $scope->getManager()->getRequestedIncludes();

        return Collection::make($includes)->map(function ($include) {
            return explode('.', $include);
        })->filter(function ($include) use ($level) {
            return count($include) > $level;
        })->pluck($level)->unique()->all();
    }

    /**
     * Resolve scoped parameters for the given scope.
     *
     * @param  \League\Fractal\Scope $scope
     * @param  string                $identifier
     * @return array
     */
    protected function resolveScopedParameters(Scope $scope, string $identifier): array
    {
        return iterator_to_array($scope->getManager()->getIncludeParams($scope->getIdentifier($identifier)));
    }

    /**
     * Get the current scope of the transformer.
     *
     * @return \League\Fractal\Scope
     */
    public abstract function getCurrentScope();

    /**
     * Include a related resource.
     *
     * @param  string $identifier
     * @param  mixed  $data
     * @param  array  $parameters
     * @return \League\Fractal\Resource\ResourceInterface
     */
    protected abstract function includeResource(string $identifier, $data, array $parameters): ResourceInterface;
}