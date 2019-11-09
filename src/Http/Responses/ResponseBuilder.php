<?php

namespace Flugg\Responder\Http\Responses;

use Flugg\Responder\Contracts\ResponseFactory;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

/**
 * An abstract builder class for building responses.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
abstract class ResponseBuilder implements Arrayable, Jsonable
{
    /**
     * A factory for making responses.
     *
     * @var \Flugg\Responder\Contracts\ResponseFactory
     */
    protected $responseFactory;

    /**
     * A HTTP status code for the response.
     *
     * @var int
     */
    protected $status;

    /**
     * Construct the builder class.
     *
     * @param \Flugg\Responder\Contracts\ResponseFactory $responseFactory
     */
    public function __construct(ResponseFactory $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    /**
     * Decorate the response with the given decorator.
     *
     * @param  string[]|string $decorator
     * @return $this
     */
    public function decorator($decorator)
    {
        $decorators = is_array($decorator) ? $decorator : func_get_args();

        foreach ($decorators as $decorator) {
            $this->responseFactory = new $decorator($this->responseFactory);
        };

        return $this;
    }

    /**
     * Respond with an HTTP response.
     *
     * @param  int|null $status
     * @param  array    $headers
     * @return \Illuminate\Http\JsonResponse
     */
    public function respond(int $status = null, array $headers = []): JsonResponse
    {
        if (! is_null($status)) {
            $this->setStatusCode($status);
        }

        return $this->responseFactory->make($this->getOutput(), $this->status, $headers);
    }

    /**
     * Convert the response to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->respond()->getData(true);
    }

    /**
     * Convert the response to an Illuminate collection.
     *
     * @return \Illuminate\Support\Collection
     */
    public function toCollection(): Collection
    {
        return new Collection($this->toArray());
    }

    /**
     * Convert the response to JSON.
     *
     * @param  int $options
     * @return string
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Set the HTTP status code for the response.
     *
     * @param  int $status
     * @return void
     */
    protected function setStatusCode(int $status)
    {
        $this->validateStatusCode($this->status = $status);
    }

    /**
     * Get the serialized response output.
     *
     * @return array
     */
    abstract protected function getOutput(): array;

    /**
     * Convert the response to an array.
     *
     * @param  int $status
     * @return void
     */
    abstract protected function validateStatusCode(int $status);
}
