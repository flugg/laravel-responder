<?php

namespace Flugg\Responder;

use Flugg\Responder\Contracts\TransformFactory;
use League\Fractal\Manager;
use League\Fractal\Resource\ResourceInterface;
use League\Fractal\Serializer\SerializerAbstract;
use LogicException;

/**
 * A factory class responsible for transforming and serializing data utilizing Fractal.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class FractalTransformFactory implements TransformFactory
{
    /**
     * A manager for executing transforms.
     *
     * @var \League\Fractal\Manager
     */
    protected $manager;

    /**
     * Construct the factory class.
     *
     * @param \League\Fractal\Manager $manager
     */
    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Transform the given resource, and serialize the data with the given serializer.
     *
     * @param  \League\Fractal\Resource\ResourceInterface    $resource
     * @param  \League\Fractal\Serializer\SerializerAbstract $serializer
     * @param  array                                         $options
     * @return array|null
     */
    public function make(ResourceInterface $resource, SerializerAbstract $serializer, array $options = [])
    {
        $options = $this->parseOptions($options, $resource);

        return $this->manager->setSerializer($serializer)
            ->parseIncludes($options['includes'])
            ->parseExcludes($options['excludes'])
            ->parseFieldsets($options['fieldsets'])
            ->createData($resource)
            ->toArray();
    }

    /**
     * Parse the transformation options.
     *
     * @param  array                                      $options
     * @param  \League\Fractal\Resource\ResourceInterface $resource
     * @return array
     */
    protected function parseOptions(array $options, ResourceInterface $resource): array
    {
        $options = array_merge([
            'includes' => [],
            'excludes' => [],
            'fieldsets' => [],
        ], $options);

        if (! empty($options['fieldsets'])) {
            if (empty($resourceKey = $resource->getResourceKey())) {
                throw new LogicException('Filtering fields using sparse fieldsets require resource key to be set.');
            }

            $options['fieldsets'] = $this->parseFieldsets($options['fieldsets'], $resourceKey, $options['includes']);
        }

        return $options;
    }

    /**
     * Parse the fieldsets for Fractal.
     *
     * @param  array  $fieldsets
     * @param  string $resourceKey
     * @param  array  $includes
     * @return array
     */
    protected function parseFieldsets(array $fieldsets, string $resourceKey, array $includes): array
    {
        $includes = array_map(function ($include) use ($resourceKey) {
            return "$resourceKey.$include";
        }, $includes);

        foreach ($fieldsets as $key => $fields) {
            if (is_numeric($key)) {
                unset($fieldsets[$key]);
                $key = $resourceKey;
            }

            $fields = $this->parseFieldset($key, (array) $fields, $includes);
            $fieldsets[$key] = array_unique(array_merge(key_exists($key, $fieldsets) ? (array) $fieldsets[$key] : [], $fields));
        }

        return array_map(function ($fields) {
            return implode(',', $fields);
        }, $fieldsets);
    }

    /**
     * Parse the given fieldset and append any related resource keys.
     *
     * @param  string $key
     * @param  array  $fields
     * @param  array  $includes
     * @return array
     */
    protected function parseFieldset(string $key, array $fields, array $includes): array
    {
        $childIncludes = array_reduce($includes, function ($segments, $include) use ($key) {
            return array_merge($segments, $this->resolveChildIncludes($key, $include));
        }, []);

        return array_merge($fields, array_unique($childIncludes));
    }

    /**
     * Resolve included segments that are a direct child to the given resource key.
     *
     * @param  string $key
     * @param  string $include
     * @return array
     */
    protected function resolveChildIncludes($key, string $include): array
    {
        if (count($segments = explode('.', $include)) <= 1) {
            return [];
        }

        $relation = $key === array_shift($segments) ? [$segments[0]] : [];

        return array_merge($relation, $this->resolveChildIncludes($key, implode('.', $segments)));
    }
}