<?php

namespace Flugg\Responder\Transformers\Concerns;

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
    public function getAvailableIncludes(): array
    {
        if ($this->relations == ['*']) {
            return $this->resolveScopedIncludes($this->getCurrentScope());
        }

        return array_keys($this->normalizeRelations($this->relations));
    }

    /**
     * Overrides Fractal's getter for default includes.
     *
     * @return array
     */
    public function getDefaultIncludes(): array
    {
        return array_keys($this->normalizeRelations($this->load));
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
        $parameters = iterator_to_array($scope->getManager()->getIncludeParams($scope->getIdentifier($identifier)));

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

        return collect($includes)->map(function ($include) {
            return explode('.', $include);
        })->filter(function ($include) use ($level) {
            return count($include) > $level;
        })->pluck($level)->unique()->all();
    }

    /**
     * Get the current scope of the transformer.
     *
     * @return \League\Fractal\Scope
     */
    public abstract function getCurrentScope();

    /**
     * Normalize relations to force a key value structure.
     *
     * @param  array $relations
     * @return array
     */
    protected abstract function normalizeRelations(array $relations): array;

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