<?php

namespace Flugg\Responder;

use Illuminate\Database\Eloquent\Relations\Pivot;
use League\Fractal\Scope;
use League\Fractal\TransformerAbstract;

/**
 * An abstract base transformer. Your transformers should extend this class, and this
 * class itself extends Fractal's transformer.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
abstract class Transformer extends TransformerAbstract
{
    /**
     * A list of all available relations.
     *
     * @var array
     */
    protected $relations = ['*'];

    /**
     * Get relations set on the transformer.
     *
     * @return array
     */
    public function getRelations():array
    {
        $relations = array_unique(array_merge($this->getAvailableIncludes(), $this->relations));

        return array_filter($relations, function($relation) {
            return $relation !== '*';
        });
    }

    /**
     * Set relations on the transformer.
     *
     * @param  array|string $relations
     * @return self
     */
    public function setRelations($relations)
    {
        $this->setAvailableIncludes(array_merge($this->availableIncludes, (array) $relations));

        return $this;
    }

    /**
     * Check if the transformer has whitelisted all relations.
     *
     * @return bool
     */
    public function allRelationsAllowed():bool
    {
        return $this->relations == ['*'];
    }

    /**
     * Call method for retrieving a relation. This method overrides Fractal's own
     * [callIncludeMethod] method to load relations directly from your models.
     *
     * @param  Scope  $scope
     * @param  string $includeName
     * @param  mixed  $data
     * @return \League\Fractal\Resource\ResourceInterface|bool
     * @throws \Exception
     */
    protected function callIncludeMethod(Scope $scope, $includeName, $data)
    {
        if ($includeName === 'pivot') {
            return $this->includePivot($data->$includeName);
        }

        $params = $scope->getManager()->getIncludeParams($scope->getIdentifier($includeName));

        if (method_exists($this, $includeName)) {
            return call_user_func([$this, $includeName], $data, $params);
        } else {
            return app(Responder::class)->transform($data->$includeName)->getResource();
        }
    }

    /**
     * Include pivot table data to the response.
     *
     * @param  Pivot $pivot
     * @return \League\Fractal\Resource\ResourceInterface|bool
     */
    protected function includePivot(Pivot $pivot)
    {
        if (! method_exists($this, 'transformPivot')) {
            return false;
        }

        return app(Responder::class)->transform($pivot, function ($pivot) {
            return $this->transformPivot($pivot);
        })->getResource();
    }
}