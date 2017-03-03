<?php

namespace Flugg\Responder;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\Manager;
use League\Fractal\Resource\ResourceInterface;
use League\Fractal\Scope;

/**
 * All transformer classes should extend this abstract base class. This class also
 * extends Fractal's abstract transformer.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class Transformation
{
    /**
     * The Fractal manager responsible for transforming and serializing data.
     *
     * @var \League\Fractal\Manager
     */
    protected $manager;

    /**
     * The Fractal resource for the transformation.
     *
     * @var \League\Fractal\Resource\ResourceInterface
     */
    protected $resource;

    /**
     * The model being transformed, which can be omitted.
     *
     * @var \Illuminate\Database\Eloquent\Model|null
     */
    protected $model;

    /**
     * Construct new transformation from the given resource and model.
     *
     * @param \League\Fractal\Manager                    $manager
     * @param \League\Fractal\Resource\ResourceInterface $resource
     * @param \Illuminate\Database\Eloquent\Model|null   $model
     */
    public function __construct(Manager $manager, ResourceInterface $resource, Model $model = null)
    {
        $this->manager = $manager;
        $this->resource = $resource;
        $this->model = $model;
    }

    /**
     * Run the transformation by transforming and serializing the resource.
     *
     * @return \League\Fractal\Scope|null
     */
    public function run():Scope
    {
        return $this->manager->parseIncludes($this->relations)->createData($this->transformation->getResource());
    }

    /**
     * Retrieve the Fractal manager instance.
     *
     * @return \League\Fractal\Manager
     */
    public function getManager():Manager
    {
        return $this->manager;
    }

    /**
     * Get the Fractal resource instance.
     *
     * @return \League\Fractal\Resource\ResourceInterface
     */
    public function getResource():ResourceInterface
    {
        return $this->resource;
    }

    /**
     * Get the model resolved from the transformation data.
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set available relations on the transformer.
     *
     * @param  array $relations
     * @param  int   $level
     * @return \Flugg\Responder\Transformation
     */
    public function setRelations(array $relations, int $level):Transformation
    {
        if (empty($this->manager->getRequestedIncludes())) {
            $this->manager->parseIncludes($relations);
        }

        $transformer = $this->resource->getTransformer();

        if ($transformer instanceof Transformer) {
            $relations = $transformer->allRelationsAllowed() ? $this->extractRelations($relations, $level) : $transformer->getRelations();
            $transformer->setAvailableIncludes($relations);
        }

        return $this;
    }

    /**
     * Extract relations for the given scope from the requested includes on the manager.
     *
     * @param  array $relations
     * @param  int   $level
     * @return array
     */
    protected function extractRelations(array $relations, int $level):array
    {
        return collect($relations)->map(function ($relation) {
            return explode('.', $relation);
        })->filter(function ($relation) use ($level) {
            return count($relation) > $level;
        })->pluck($level)->unique()->toArray();
    }
}