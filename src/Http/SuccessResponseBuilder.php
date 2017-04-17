<?php

namespace Flugg\Responder\Http;

use Flugg\Responder\Exceptions\InvalidSerializerException;
use Flugg\Responder\Transformation;
use Flugg\Responder\TransformationFactory;
use Flugg\Responder\Transformer;
use InvalidArgumentException;
use League\Fractal\Serializer\SerializerAbstract;

/**
 * This class is an abstract response builder and hold common functionality the success-
 * and error response buuilder classes.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class SuccessResponseBuilder extends ResponseBuilder
{
    /**
     * The included relations.
     *
     * @var array
     */
    protected $relations = [];

    /**
     * The HTTP status code for the response.
     *
     * @var int
     */
    protected $statusCode = 200;

    /**
     * The transformation factory used to build transformations.
     *
     * @var \Flugg\Responder\TransformationFactory
     */
    protected $transformationFactory;

    /**
     * The transformation object holding the root scope resource.
     *
     * @var \Flugg\Responder\Transformation
     */
    protected $transformation;

    /**
     * SuccessResponseBuilder constructor.
     *
     * @param \Illuminate\Contracts\Routing\ResponseFactory|\Laravel\Lumen\Http\ResponseFactory $responseFactory
     * @param \Flugg\Responder\TransformationFactory                                            $transformationFactory
     */
    public function __construct($responseFactory, TransformationFactory $transformationFactory)
    {
        $this->transformationFactory = $transformationFactory;
        $this->transformation = $this->transformationFactory->make();

        parent::__construct($responseFactory);
    }

    /**
     * Set transformation data and transformer.
     *
     * @param  mixed|null           $data
     * @param  callable|string|null $transformer
     * @param  string|null          $resourceKey
     * @return self
     */
    public function transform($data = null, $transformer = null, string $resourceKey = null):SuccessResponseBuilder
    {
        $this->transformation = $this->transformationFactory->make($data, $transformer, $resourceKey);

        return $this;
    }

    /**
     * Add data to the meta data appended to the response data.
     *
     * @param  array $data
     * @return self
     */
    public function addMeta(array $data):SuccessResponseBuilder
    {
        $meta = array_merge($this->transformation->getResource()->getMeta(), $data);

        $this->transformation->getResource()->setMeta($meta);

        return $this;
    }

    /**
     * Set the serializer used to serialize the resource data.
     *
     * @param  \League\Fractal\Serializer\SerializerAbstract|string $serializer
     * @return self
     */
    public function serializer($serializer):SuccessResponseBuilder
    {
        $serializer = is_string($serializer) ? new $serializer : $serializer;

        if (! $serializer instanceof SerializerAbstract) {
            throw new InvalidSerializerException();
        }

        $this->transformation->getManager()->setSerializer($serializer);

        return $this;
    }

    /**
     * Set the included relationships.
     *
     * @param  array|string $relations
     * @return self
     */
    public function with($relations):SuccessResponseBuilder
    {
        if (is_string($relations)) {
            $relations = explode(',', $relations);
        }

        $this->relations = array_merge($this->relations, (array) $relations);

        return $this;
    }

    /**
     * Set the HTTP status code for the response.
     *
     * @param  int $statusCode
     * @return self
     * @throws \InvalidArgumentException
     */
    public function setStatus(int $statusCode)
    {
        if ($statusCode < 100 || $statusCode >= 400) {
            throw new InvalidArgumentException("{$statusCode} is not a valid success HTTP status code.");
        }

        return parent::setStatus($statusCode);
    }

    /**
     * Convert the response to an array by running the transformation.
     *
     * @return array
     */
    public function toArray():array
    {
        if (! is_null($model = $this->transformation->getModel())) {
            $this->with($this->extractDefaultRelations($this->transformation->getResource()->getTransformer()));

            $this->transformation->getResource()->getData()->load($this->relations);
        }

        return $this->transformation->setRelations($this->relations)->run()->toArray();
    }

    /**
     * Extract default relations.
     *
     * @param  \Flugg\Responder\Transformer $transformer
     * @return array
     */
    protected function extractDefaultRelations(Transformer $transformer):array
    {
        $relations = collect(array_keys($transformer->getDefaultRelations()));

        foreach ($transformer->getDefaultRelations() as $relation => $relatedTransformer) {
            $nestedRelations = collect($this->extractDefaultRelations(app($relatedTransformer)))->map(function ($nestedRelation) use ($relation) {
                return "$relation.$nestedRelation";
            });

            $relations = $relations->merge($nestedRelations);
        }

        return $relations->all();
    }

    /**
     * Retrieve the root transformation instance.
     *
     * @return \Flugg\Responder\Transformation
     */
    public function getTransformation():Transformation
    {
        return $this->transformation;
    }
}