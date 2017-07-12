<?php

namespace Flugg\Responder\Transformers\Concerns;

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
        return $this->availableIncludes;
    }

    /**
     * Overrides Fractal's getter for default includes.
     *
     * @return array
     */
    public function getDefaultIncludes()
    {
        return array_keys($this->with);
    }

    /**
     * Overrides Fractal's method for including a relation.
     *
     * @param  \League\Fractal\Scope $scope
     * @param  string                $relation
     * @param  mixed                 $data
     * @return \League\Fractal\Resource\ResourceInterface|false
     */
    protected function callIncludeMethod(Scope $scope, $relation, $data)
    {
        $parameters = $this->getScopedParameters($scope, $relation);
        $resource = $this->makeResource($relation, $data, $parameters);

        return $resource;
    }
}