<?php

namespace Flugg\Responder;

use Flugg\Responder\Contracts\Transformer as TransformerContract;
use Flugg\Responder\Serializers\NullSerializer;

/**
 * A service class responsible for transforming data without serializing.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class Transformer implements TransformerContract
{
    /**
     * A builder used to build transformed arrays.
     *
     * @var \Flugg\Responder\TransformBuilder
     */
    protected $transformBuilder;

    /**
     * Construct the service class.
     *
     * @param \Flugg\Responder\TransformBuilder $transformBuilder
     */
    public function __construct(TransformBuilder $transformBuilder)
    {
        $this->transformBuilder = $transformBuilder;
    }

    /**
     * Transform the data without serializing with the given transformer and relations.
     *
     * @param  mixed                                                          $data
     * @param  \Flugg\Responder\Transformers\Transformer|callable|string|null $transformer
     * @param  string[]                                                       $with
     * @param  string[]                                                       $without
     * @return array
     */
    public function transform($data = null, $transformer = null, array $with = [], array $without = []): array
    {
        return $this->transformBuilder->resource($data, $transformer)
            ->with($with)
            ->without($without)
            ->serializer(new NullSerializer)
            ->transform();
    }
}