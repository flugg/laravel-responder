<?php

namespace Flugg\Responder;

use Flugg\Responder\Serializers\NoopSerializer;

/**
 * A service class responsible for just transforming data, without the serializing.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class SimpleTransformer
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
     * Transform the data without serializing, using the given transformer.
     *
     * @param  mixed                                                          $data
     * @param  \Flugg\Responder\Transformers\Transformer|callable|string|null $transformer
     * @return \Flugg\Responder\TransformBuilder
     */
    public function make($data = null, $transformer = null): TransformBuilder
    {
        return $this->transformBuilder->resource($data, $transformer)->serializer(new NoopSerializer);
    }
}