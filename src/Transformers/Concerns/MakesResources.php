<?php

namespace Flugg\Responder\Transformers\Concerns;

use Closure;
use Illuminate\Database\Eloquent\Model;
use League\Fractal\ParamBag;
use League\Fractal\Scope;

/**
 * A trait to be used by a transformer to make resources for relations
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
trait MakesResources
{
    /**
     * A list of cached related resources.
     *
     * @var \League\Fractal\ResourceInterface
     */
    protected $resources = [];

    /**
     * The resource builder resolver callback.
     *
     * @var \Closure|null
     */
    protected static $resourceBuilderResolver;

    /**
     * Set a resource builder using a resolver callback.
     *
     * @param  null                                                           $data
     * @param  \Flugg\Responder\Transformers\Transformer|string|callable|null $transformer
     * @param  string|null                                                    $resourceKey
     * @return void
     */
    public function resource($data = null, $transformer = null, string $resourceKey = null)
    {
        return static::resolveResourceBuilder()->make($data, $transformer)->withResourceKey($resourceKey)->get();
    }

    /**
     * Set a resource builder using a resolver callback.
     *
     * @param  \Closure $resolver
     * @return void
     */
    public static function resourceBuilderResolver(Closure $resolver)
    {
        static::$resourceBuilderResolver = $resolver;
    }

    /**
     * Resolve a resource builder using the resolver.
     *
     * @return \Flugg\Responder\Resources\ResourceBuilder
     */
    protected static function resolveResourceBuilder()
    {
        return call_user_func(static::$currentCursorResolver, $name);
    }

    /**
     * Make a related resource.
     *
     * @param  string                  $relation
     * @param  mixed                   $data
     * @param \League\Fractal\ParamBag $parameters
     * @return \League\Fractal\Resource\ResourceInterface|false
     */
    protected function makeResource(string $relation, $data, ParamBag $parameters)
    {
        if (method_exists($this, $method = 'include' . ucfirst($relation))) {
            return $this->$method($data, $parameters);
        }

        return $data instanceof Model ? $this->makeResourceFromModel() : false;
    }

    /**
     * Make a related resource from model data.
     *
     * @param  string                              $relation
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @return \League\Fractal\Resource\ResourceInterface|bool
     */
    protected function makeResourceFromModel(string $relation, Model $model)
    {
        $data = $model->$relation;

        if (key_exists($relation, $this->resources)) {
            return $this->resources[$relation]->setData($data);
        }

        return $this->resource($data);
    }

    /**
     * Get parameters for a relation.
     *
     * @param  \League\Fractal\Scope $scope
     * @param  string                $relation
     * @return \League\Fractal\ParamBag
     */
    protected function getScopedParameters(Scope $scope, string $relation): ParamBag
    {
        return $scope->getManager()->getIncludeParams($scope->getIdentifier($relation));
    }
}