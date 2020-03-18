<?php

namespace Flugg\Responder\Http\Builders;

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
 * Builder class for building success responses.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class SuccessResponseBuilder extends ResponseBuilder
{
    /**
     * Response value object.
     *
     * @var SuccessResponse
     */
    protected $response;

    /**
     * Paginator value object.
     *
     * @var Paginator
     */
    protected $paginator;

    /**
     * Cursor paginator value object.
     *
     * @var CursorPaginator
     */
    protected $cursorPaginator;

    /**
     * Constant defining the status code used if nothing is set.
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
        $this->response = $this->normalizeData($data);

        return $this;
    }

    /**
     * Attempt to make a paginator from the given data.
     *
     * @param mixed $data
     * @return SuccessResponse
     * @throws InvalidDataException
     */
    protected function normalizeData($data): SuccessResponse
    {
        if (is_array($data)) {
            return (new SuccessResponse())->setData($data);
        }

        foreach ($this->normalizers as $class => $normalizer) {
            if ($data instanceof $class) {
                return (new $normalizer())->normalize($data);
            }
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
