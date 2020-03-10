<?php

namespace Flugg\Responder\Http\Builders;

use Flugg\Responder\Contracts\AdapterFactory;
use Flugg\Responder\Contracts\Http\Builders\ResponseBuilder as ResponseBuilderContract;
use Flugg\Responder\Contracts\Http\Factories\ResponseFactory;
use Flugg\Responder\Contracts\Http\Formatters\ResponseFormatter;
use Flugg\Responder\Exceptions\InvalidStatusCodeException;
use Flugg\Responder\Http\Response;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use JsonSerializable;

/**
 * An abstract builder class for building responses.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
abstract class ResponseBuilder implements ResponseBuilderContract, Responsable, Arrayable, Jsonable, JsonSerializable
{
    /**
     * A factory for making JSON responses.
     *
     * @var ResponseFactory
     */
    protected $responseFactory;

    /**
     * A factory for making adapters.
     *
     * @var AdapterFactory
     */
    protected $adapterFactory;

    /**
     * A response object.
     *
     * @var Response
     */
    protected $response;

    /**
     * A response formatter.
     *
     * @var ResponseFormatter
     */
    protected $formatter;

    /**
     * Create a new response builder instance.
     *
     * @param ResponseFactory $responseFactory
     * @param AdapterFactory $adapterFactory
     */
    public function __construct(ResponseFactory $responseFactory, AdapterFactory $adapterFactory)
    {
        $this->responseFactory = $responseFactory;
        $this->adapterFactory = $adapterFactory;
    }

    /**
     * Set a response formatter.
     *
     * @param ResponseFormatter|string $formatter
     * @return $this
     */
    public function formatter($formatter)
    {
        $this->formatter = is_string($formatter) ? new $formatter : $formatter;

        return $this;
    }

    /**
     * Decorate the response with the given decorators.
     *
     * @param string|string[] $decorators
     * @return $this
     */
    public function decorate($decorators)
    {
        $decorators = is_array($decorators) ? $decorators : func_get_args();

        foreach ($decorators as $decorator) {
            $this->responseFactory = new $decorator($this->responseFactory);
        }

        return $this;
    }

    /**
     * Add additional meta data to the response content.
     *
     * @param array $meta
     * @return $this
     */
    public function meta(array $meta): self
    {
        $this->response->setMeta($meta);

        return $this;
    }

    /**
     * Respond with a JSON response.
     *
     * @param int|null $status
     * @param array $headers
     * @return JsonResponse
     * @throws InvalidStatusCodeException
     */
    public function respond(int $status = null, array $headers = []): JsonResponse
    {
        if (is_int($status)) {
            $this->response->setStatus($status);
        }

        $this->response->setHeaders(array_merge($this->response->headers(), $headers));

        return $this->toResponse(app('request'));
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function toResponse($request): JsonResponse
    {
        return $this->responseFactory->make($this->content(), $this->response->status(), $this->response->headers());
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
     * @return Collection
     */
    public function toCollection(): Collection
    {
        return Collection::make($this->toArray());
    }

    /**
     * Convert the response to JSON.
     *
     * @param int $options
     * @return string
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
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
     * Get the response content.
     *
     * @return array
     */
    abstract protected function content(): array;
}
