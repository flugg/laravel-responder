<?php

namespace Flugg\Responder\Transformers\Concerns;

use Countable;
use Flugg\Responder\Contracts\Resources\ResourceFactory;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
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
     * @param  mixed                                                          $data
     * @param  \Flugg\Responder\Transformers\Transformer|string|callable|null $transformer
     * @param  string|null                                                    $resourceKey
     * @return \League\Fractal\Resource\ResourceInterface
     */
    protected function resource($data = null, $transformer = null, string $resourceKey = null): ResourceInterface
    {
        if ($data instanceof ResourceInterface) {
            return $data;
        }

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
        $transformer = $this->mappedTransformerClass($identifier);

        if(config('responder.use_camel_case_relations')) {
            $identifier = Str::camel($identifier);
        }

        if (method_exists($this, $method = 'include' . ucfirst($identifier))) {
            $resource = $this->resource($this->$method($data, collect($parameters)), $transformer, $identifier);
        } elseif ($data instanceof Model) {
            $resource = $this->includeResourceFromModel($data, $identifier, $transformer);
        } else {
            throw new LogicException('Relation [' . $identifier . '] not found in [' . get_class($this) . '].');
        }

        return $resource;
    }

    /**
     * Include a related resource from a model and cache the resource type for following calls.
     *
     * @param  \Illuminate\Database\Eloquent\Model                            $model
     * @param  string                                                         $identifier
     * @param  \Flugg\Responder\Transformers\Transformer|string|callable|null $transformer
     * @return \League\Fractal\Resource\ResourceInterface
     */
    protected function includeResourceFromModel(Model $model, string $identifier, $transformer = null): ResourceInterface
    {
        $data = $this->resolveRelation($model, $identifier);

        if (! $this->shouldCacheResource($data)) {
            return $this->resource($data, $transformer, $identifier);
        } elseif (key_exists($identifier, $this->resources)) {
            return $this->resources[$identifier]->setData($data);
        }

        return $this->resources[$identifier] = $this->resource($data, $transformer, $identifier);
    }

    /**
     * Indicates if the resource should be cached.
     *
     * @param  mixed $data
     * @return bool
     */
    protected function shouldCacheResource($data): bool
    {
        return is_array($data) || $data instanceof Countable ? count($data) > 0 : is_null($data);
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

    /**
     * Get a related transformer class mapped to a relation identifier.
     *
     * @param  string $identifier
     * @return string
     */
    protected abstract function mappedTransformerClass(string $identifier);
}