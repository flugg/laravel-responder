<?php

namespace Flugg\Responder\Http\Builders;

use Flugg\Responder\Contracts\Http\Builders\SuccessResponseBuilder as SuccessResponseBuilderContract;
use Flugg\Responder\Contracts\Pagination\CursorPaginator;
use Flugg\Responder\Contracts\Pagination\Paginator;
use Flugg\Responder\Exceptions\InvalidDataException;
use Flugg\Responder\Exceptions\InvalidStatusCodeException;
use Flugg\Responder\Http\SuccessResponse;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A builder class for building success responses.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class SuccessResponseBuilder extends ResponseBuilder implements SuccessResponseBuilderContract
{
    /**
     * A response object.
     *
     * @var SuccessResponse
     */
    protected $response;

    /**
     * A paginator object.
     *
     * @var Paginator
     */
    protected $paginator;

    /**
     * A cursor paginator object.
     *
     * @var CursorPaginator
     */
    protected $cursorPaginator;

    /**
     * A constant defining the status code used if nothing is set.
     *
     * @var int
     */
    protected const DEFAULT_STATUS = 200;

    /**
     * Make a success response from the given data.
     *
     * @param array|Arrayable|Builder|JsonResource|QueryBuilder|Relation $data
     * @return $this
     * @throws InvalidDataException
     * @throws InvalidStatusCodeException
     */
    public function data($data = [])
    {
        if ($data instanceof JsonResource) {
            $this->setPaginatorFromData($data->resource);
            $this->response = $this->makeResponseFromResource($data);
        } else {
            $this->setPaginatorFromData($data);
            $this->response = $this->makeResponse($this->normalizeData($data), self::DEFAULT_STATUS);
        }

        return $this;
    }

    /**
     * Attempt to make a paginator from the given data.
     *
     * @param array|Arrayable|Builder|JsonResource|QueryBuilder|Relation $data
     * @return array
     * @throws InvalidDataException
     */
    protected function normalizeData($data): array
    {
        if (is_array($data)) {
            return $data;
        } elseif ($data instanceof Arrayable) {
            return $data->toArray();
        } elseif ($data instanceof QueryBuilder || $data instanceof Builder) {
            return $data->get()->toArray();
        } elseif ($data instanceof Relation) {
            return $data->getResults();
        }

        throw new InvalidDataException;
    }

    /**
     * Attempt to set a paginator from the given data using an adapter.
     *
     * @param array|Arrayable|Builder|JsonResource|QueryBuilder|Relation $data
     * @return void
     */
    protected function setPaginatorFromData($data): void
    {
        if ($paginator = $this->adapterFactory->makePaginator($data)) {
            $this->paginator = $paginator;
        } elseif ($cursorPaginator = $this->adapterFactory->makeCursorPaginator($data)) {
            $this->cursorPaginator = $cursorPaginator;
        }
    }

    /**
     * Make a success response from the resource.
     *
     * @param JsonResource $resource
     * @return SuccessResponse
     * @throws InvalidStatusCodeException
     */
    protected function makeResponseFromResource(JsonResource $resource): SuccessResponse
    {
        $response = $resource->response();
        $meta = array_merge_recursive($resource->with(app('request')), $resource->additional);

        return $this->makeResponse($resource->resolve(), $response->status(), $response->headers->all())->setMeta($meta);
    }

    /**
     * Make a success response.
     *
     * @param array $data
     * @param int $status
     * @param array $headers
     * @return SuccessResponse
     * @throws InvalidStatusCodeException
     */
    protected function makeResponse(array $data, int $status, array $headers = []): SuccessResponse
    {
        return (new SuccessResponse)->setStatus($status)->setHeaders($headers)->setData($data);
    }

    /**
     * Get the response content.
     *
     * @return array
     */
    protected function content(): array
    {
        if (!$this->formatter) {
            return $this->response->data();
        }

        return $this->format($this->response);
    }

    /**
     * Format the response data.
     *
     * @param SuccessResponse $response
     * @return array
     */
    protected function format(SuccessResponse $response): array
    {
        $data = $this->formatter->success($response);

        if ($this->paginator) {
            $data = $this->formatter->paginator($data, $this->paginator);
        }

        if ($this->cursorPaginator) {
            $data = $this->formatter->cursor($data, $this->cursorPaginator);
        }

        return $data;
    }
}
