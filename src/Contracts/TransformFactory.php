<?php

namespace Flugg\Responder\Contracts;

use League\Fractal\Resource\ResourceInterface;
use League\Fractal\Serializer\SerializerAbstract;

/**
 * A contract for transforming and serializing data.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
interface TransformFactory
{
    /**
     * Transform the given resource, and serialize the data with the given serializer.
     *
     * @param  \League\Fractal\Resource\ResourceInterface    $resource
     * @param  \League\Fractal\Serializer\SerializerAbstract $serializer
     * @param  array                                         $options
     * @return array|null
     */
    public function make(ResourceInterface $resource, SerializerAbstract $serializer, array $options = []);
}