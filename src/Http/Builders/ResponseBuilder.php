<?php

namespace Flugg\Responder\Http\Builders;

use Flugg\Responder\Contracts\Http\ResponseFactory;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use JsonSerializable;

/**
 * Abstract builder class for building responses.
 */
abstract class ResponseBuilder implements Responsable, Arrayable, Jsonable, JsonSerializable
{
    /**
     * Factory for making JSON responses.
     *
     * @var \Flugg\Responder\Contracts\Http\ResponseFactory
     */
    protected $responseFactory;

    /**
     * A service container.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * Config repository.
     *
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    /**
     * Response value object.
     *
     * @var \Flugg\Responder\Http\Response
     */
    protected $response;

    /**
     * Response formatter.
     *
     * @var \Flugg\Responder\Contracts\Http\Formatter|null
     */
    protected $formatter;

    /**
     * Create a new response builder instance.
     *
     * @param \Flugg\Responder\Contracts\Http\ResponseFactory $responseFactory
     * @param \Illuminate\Contracts\Container\Container $container
     * @param \Illuminate\Contracts\Config\Repository $config
     */
    public function __construct(ResponseFactory $responseFactory, Container $container, Repository $config)
    {
        $this->responseFactory = $responseFactory;
        $this->container = $container;
        $this->config = $config;
    }

    /**
     * Set a response formatter.
     *
     * @param \Flugg\Responder\Contracts\Http\Formatter|string|null $formatter
     * @return $this
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function formatter($formatter)
    {
        $this->formatter = is_string($formatter) ? $this->container->make($formatter) : $formatter;

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
     * Attach metadata to the response content.
     *
     * @param array $meta
     * @return $this
     */
    public function meta(array $meta)
    {
        $this->response->setMeta($meta);

        return $this;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Flugg\Responder\Exceptions\InvalidStatusCodeException
     */
    public function toResponse($request): JsonResponse
    {
        return $this->respond();
    }

    /**
     * Respond with a JSON response.
     *
     * @param int|null $status
     * @param array $headers
     * @return \Illuminate\Http\JsonResponse
     * @throws \Flugg\Responder\Exceptions\InvalidStatusCodeException
     */
    public function respond(int $status = null, array $headers = []): JsonResponse
    {
        if (is_int($status)) {
            $this->response->setStatus($status);
        }

        $this->response->setHeaders(array_merge($this->response->headers(), $headers));

        return $this->responseFactory->make($this->format(), $this->response->status(), $this->response->headers());
    }

    /**
     * Convert the response to an array.
     *
     * @return array
     * @throws \Flugg\Responder\Exceptions\InvalidStatusCodeException
     */
    public function toArray(): array
    {
        return $this->respond()->getData(true);
    }

    /**
     * Convert the response to an Illuminate collection.
     *
     * @return \Illuminate\Support\Collection
     * @throws \Flugg\Responder\Exceptions\InvalidStatusCodeException
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
     * @throws \Flugg\Responder\Exceptions\InvalidStatusCodeException
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     * @throws \Flugg\Responder\Exceptions\InvalidStatusCodeException
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Format the response data.
     *
     * @return array
     */
    abstract protected function format(): array;
}
