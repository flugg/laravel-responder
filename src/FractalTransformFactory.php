<?php

namespace Flugg\Responder;

use Flugg\Responder\Contracts\TransformFactory;
use League\Fractal\Manager;
use League\Fractal\Resource\ResourceInterface;
use League\Fractal\Serializer\SerializerAbstract;

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
     * @return array
     */
    public function make(ResourceInterface $resource, SerializerAbstract $serializer, array $options = []): array
    {
        return $this->manager->setSerializer($serializer)
            ->parseIncludes($options['includes'] ?? [])
            ->parseExcludes($options['excludes'] ?? [])
            ->parseFieldsets($options['fields'] ?? [])
            ->createData($resource)
            ->toArray();
    }
}