<?php

namespace Flugg\Responder\Transformers\Concerns;

use Flugg\Responder\Contracts\Resources\ResourceFactory;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Eloquent\Model;
use League\Fractal\Resource\ResourceInterface;
use LogicException;

/**
 * A trait to be used by a transformer to make related resources.
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
     * @var \League\Fractal\ResourceInterface[]
     */
    protected $resources = [];

    /**
     * Make a resource.
     *
     * @param  null                                                           $data
     * @param  \Flugg\Responder\Transformers\Transformer|string|callable|null $transformer
     * @param  string|null                                                    $resourceKey
     * @return \League\Fractal\Resource\ResourceInterface
     */
    protected function resource($data = null, $transformer = null, string $resourceKey = null): ResourceInterface
    {
        $resourceFactory = $this->resolveContainer()->make(ResourceFactory::class);

        return $resourceFactory->make($data, $transformer, $resourceKey);
    }

    /**
     * Include a related resource.
     *
     * @param  string $identifier
     * @param  mixed  $data
     * @param  array  $parameters
     * @return \League\Fractal\Resource\ResourceInterface
     * @throws \LogicException
     */
    protected function includeResource(string $identifier, $data, array $parameters): ResourceInterface
    {
        if (method_exists($this, $method = 'include' . ucfirst($identifier))) {
            $resource = $this->$method($data, $parameters);
        } elseif ($data instanceof Model) {
            $resource = $this->includeResourceFromModel($data, $identifier);
        } else {
            throw new LogicException('Relation [' . $identifier . '] not found in [' . get_class($this) . '].');
        }

        return $resource;
    }

    /**
     * Include a related resource from a model.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string                              $identifier
     * @return \League\Fractal\Resource\ResourceInterface
     */
    protected function includeResourceFromModel(Model $model, string $identifier): ResourceInterface
    {
        $data = $this->resolveRelation($model, $identifier);

        if (! count($data)) {
            return $this->resource($data, null, $identifier);
        } elseif (key_exists($identifier, $this->resources)) {
            return $this->resources[$identifier]->setData($data);
        }

        return $this->resources[$identifier] = $this->resource($data, null, $identifier);
    }

    /**
     * Resolve a container using the resolver callback.
     *
     * @return \Illuminate\Contracts\Container\Container
     */
    protected abstract function resolveContainer(): Container;

    /**
     * Resolve relation data from a model.
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @param  string                              $identifier
     * @return mixed
     */
    protected abstract function resolveRelation(Model $model, string $identifier);
}