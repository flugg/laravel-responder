<?php

namespace Flugg\Responder\Http;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use JsonSerializable;

/**
 * This class is an abstract response builder and hold common functionality the success-
 * and error response buuilder classes.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
abstract class ResponseBuilder implements Arrayable, Jsonable, JsonSerializable
{
    /**
     * Flag indicating if status code should be added to the serialized data.
     *
     * @var bool
     */
    protected $includeStatusCode;

    /**
     * The HTTP status code for the response.
     *
     * @var int
     */
    protected $statusCode;

    /**
     * Response factory used to generate JSON responses.
     *
     * @var \Illuminate\Contracts\Routing\ResponseFactory
     */
    protected $responseFactory;

    /**
     * Constructor.
     *
     * @param \Illuminate\Contracts\Routing\ResponseFactory $responseFactory
     */
    public function __construct(ResponseFactory $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    /**
     * Serialize the data and wrap it in a JSON response object.
     *
     * @param  int|null $statusCode
     * @param  array    $headers
     * @return \Illuminate\Http\JsonResponse
     */
    public function respond(int $statusCode = null, array $headers = []):JsonResponse
    {
        if (! is_null($statusCode)) {
            $this->setStatus($statusCode);
        }

        $data = $this->includeStatusCode($this->toArray());

        return $this->responseFactory->json($data, $this->statusCode, $headers);
    }

    /**
     * Set the HTTP status code for the response.
     *
     * @param  int $statusCode
     * @return self
     */
    public function setStatus(int $statusCode):ResponseBuilder
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * Set a flag indicating if status code should be added to the response.
     *
     * @param  bool $includeStatusCode
     * @return self
     */
    public function setIncludeStatusCode(bool $includeStatusCode):ResponseBuilder
    {
        $this->includeStatusCode = $includeStatusCode;

        return $this;
    }

    /**
     * Convert the response to an Illuminate collection.
     *
     * @return \Illuminate\Support\Collection
     */
    public function toCollection():Collection
    {
        return new Collection($this->toArray());
    }

    /**
     * Convert the response to JSON.
     *
     * @param  int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Convert the response to an array.
     *
     * @return array
     */
    abstract public function toArray():array;

    /**
     * Include a status code to the serialized data if enabled.
     *
     * @param  array $data
     * @return array
     */
    protected function includeStatusCode(array $data):array
    {
        if (! $this->includeStatusCode) {
            return $data;
        }

        return array_merge(['status' => $this->statusCode], $data);;
    }
}