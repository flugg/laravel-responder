<?php

namespace Flugg\Responder\Http\Formatters;

use Flugg\Responder\Contracts\Http\Formatter;
use Flugg\Responder\Contracts\Pagination\CursorPaginator;
use Flugg\Responder\Contracts\Pagination\Paginator;
use Flugg\Responder\Contracts\Validation\Validator;
use Flugg\Responder\Http\ErrorResponse;
use Flugg\Responder\Http\Resources\Collection;
use Flugg\Responder\Http\Resources\Item;
use Flugg\Responder\Http\Resources\Primitive;
use Flugg\Responder\Http\Resources\Resource;
use Flugg\Responder\Http\SuccessResponse;
use Illuminate\Support\Collection as IlluminateCollection;

/**
 * Simple response formatter.
 */
class SimpleFormatter implements Formatter
{
    /**
     * Format success response data.
     *
     * @param \Flugg\Responder\Http\SuccessResponse $response
     * @return array
     */
    public function success(SuccessResponse $response): array
    {
        $resource = $response->resource();
        $resourceKey = ($resource ? $resource->key() : null) ?: 'data';
        $data = array_merge([$resourceKey => $this->data($resource)], $response->meta());

        if ($paginator = $response->paginator()) {
            $data['pagination'] = $this->paginator($paginator);
        } elseif ($paginator = $response->cursor()) {
            $data['cursor'] = $this->cursor($paginator);
        }

        return $data;
    }

    /**
     * Format error response data.
     *
     * @param \Flugg\Responder\Http\ErrorResponse $response
     * @return array
     */
    public function error(ErrorResponse $response): array
    {
        $data = array_merge(['error' => ['code' => $response->code()]], $response->meta());

        if ($message = $response->message()) {
            $data['error']['message'] = $message;
        }

        if ($validator = $response->validator()) {
            $data['error'] = array_merge($data['error'], $this->validator($validator));
        }

        return $data;
    }

    /**
     * Format success data structure from a resource.
     *
     * @param \Flugg\Responder\Http\Resources\Resource|null $resource
     * @return mixed
     */
    protected function data(?Resource $resource)
    {
        if ($resource instanceof Item) {
            return $this->item($resource);
        } elseif ($resource instanceof Collection) {
            return $this->collection($resource);
        } elseif ($resource instanceof Primitive) {
            return $resource->data();
        }

        return null;
    }

    /**
     * Format an item resource.
     *
     * @param \Flugg\Responder\Http\Resources\Item $item
     * @return array
     */
    protected function item(Item $item): array
    {
        return array_merge($item->data(), IlluminateCollection::make($item->relations())
            ->mapWithKeys(function ($value, $key) {
                return [$key => $value instanceof Item ? $this->item($value) : $this->collection($value)];
            })->toArray());
    }

    /**
     * Format an item collection.
     *
     * @param \Flugg\Responder\Http\Resources\Collection $collection
     * @return array
     */
    protected function collection(Collection $collection): array
    {
        return array_map([$this, 'item'], $collection->items());
    }

    /**
     * Format pagination metadata.
     *
     * @param \Flugg\Responder\Contracts\Pagination\Paginator $paginator
     * @return array
     */
    protected function paginator(Paginator $paginator): array
    {
        $pagination = [
            'count' => $paginator->count(),
            'total' => $paginator->total(),
            'perPage' => $paginator->perPage(),
            'currentPage' => $currentPage = $paginator->currentPage(),
            'lastPage' => $lastPage = $paginator->lastPage(),
            'links' => [
                'self' => $paginator->url($currentPage),
                'first' => $paginator->url(1),
                'last' => $paginator->url($lastPage),
            ],
        ];

        if ($currentPage > 1) {
            $pagination['links']['prev'] = $paginator->url($currentPage - 1);
        }

        if ($currentPage < $lastPage) {
            $pagination['links']['next'] = $paginator->url($currentPage + 1);
        }

        return $pagination;
    }

    /**
     * Format cursor pagination metadata.
     *
     * @param \Flugg\Responder\Contracts\Pagination\CursorPaginator $paginator
     * @return array
     */
    protected function cursor(CursorPaginator $paginator): array
    {
        return [
            'current' => $paginator->current(),
            'previous' => $paginator->previous(),
            'next' => $paginator->next(),
            'count' => $paginator->count(),
        ];
    }

    /**
     * Format validator metadata.
     *
     * @param \Flugg\Responder\Contracts\Validation\Validator $validator
     * @return array
     */
    protected function validator(Validator $validator): array
    {
        return [
            'fields' => array_reduce($validator->failed(), function ($fields, $field) use ($validator) {
                return array_merge($fields, [
                    $field => array_map(function ($rule) use ($field, $validator) {
                        return [
                            'rule' => $rule,
                            'message' => $validator->messages()["$field.$rule"],
                        ];
                    }, $validator->errors()[$field]),
                ]);
            }, []),
        ];
    }
}
