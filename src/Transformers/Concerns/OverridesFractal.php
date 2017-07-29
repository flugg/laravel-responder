<?php

namespace Flugg\Responder\Transformers\Concerns;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\ParamBag;
use League\Fractal\Scope;
use LogicException;

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
        return $this->availableIncludes ?: [];
    }

    /**
     * Overrides Fractal's getter for default includes.
     *
     * @return array
     */
    public function getDefaultIncludes()
    {
        return array_keys($this->load ?: []);
    }

    /**
     * Overrides Fractal's method for including a relation.
     *
     * @param  \League\Fractal\Scope $scope
     * @param  string                $relation
     * @param  mixed                 $data
     * @return \League\Fractal\Resource\ResourceInterface
     * @throws \LogicException
     */
    protected function callIncludeMethod(Scope $scope, $relation, $data)
    {
        $parameters = $this->getScopedParameters($scope, $relation);

        if ($method = $this->getIncludeMethod($relation)) {
            return $this->$method($this->filterData($data, $relation), $parameters);
        } elseif ($data instanceof Model) {
            return $this->makeResource($relation, $this->filterData($data->$relation, $relation));
        }

        throw new LogicException('Cannot resolve relation [' . $relation . '] in [' . self::class . ']');
    }

    /**
     * Get scoped parameters for a relation.
     *
     * @param  \League\Fractal\Scope $scope
     * @param  string                $relation
     * @return \League\Fractal\ParamBag
     */
    protected function getScopedParameters(Scope $scope, string $relation): ParamBag
    {
        return $scope->getManager()->getIncludeParams($scope->getIdentifier($relation));
    }

    /**
     * Get the name of an existing include method.
     *
     * @param  string $relation
     * @return string|null
     */
    protected function getIncludeMethod(string $relation)
    {
        return method_exists($this, $method = 'include' . ucfirst($relation)) ? $method : null;
    }

    /**
     * Filter data using a filter method.
     *
     * @param  mixed  $data
     * @param  string $relation
     * @return mixed
     */
    protected function filterData($data, string $relation)
    {
        if (! $method = $this->getFilterMethod($relation)) {
            return $data;
        }

        return $method($data);

    }

    /**
     * Get the name of an existing filter method.
     *
     * @param  string $relation
     * @return string|null
     */
    protected function getFilterMethod(string $relation)
    {
        return method_exists($this, $method = 'filter' . ucfirst($relation)) ? $method : null;
    }

    /**
     * Make a related resource.
     *
     * @param  string $relation
     * @param  mixed  $data
     * @return \League\Fractal\Resource\ResourceInterface|false
     */
    protected abstract function makeResource(string $relation, $data);
}