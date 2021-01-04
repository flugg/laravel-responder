<?php

namespace Flugg\Responder\Http\Formatters;

use Flugg\Responder\Contracts\Http\Formatter;
use Flugg\Responder\Contracts\Pagination\CursorPaginator;
use Flugg\Responder\Contracts\Pagination\Paginator;
use Flugg\Responder\Contracts\Validation\Validator;
use Flugg\Responder\Http\ErrorResponse;
use Flugg\Responder\Http\Resources\Collection;
use Flugg\Responder\Http\Resources\Item;
use Flugg\Responder\Http\SuccessResponse;
use Illuminate\Support\Arr;
use InvalidArgumentException;

/**
 * Response formatter following the JSON API specification.
 */
class JsonApiFormatter implements Formatter
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

        return [
            'data' => $resource instanceof Collection
                ? array_map([$this, 'resource'], $resource->items())
                : $this->resource($resource),
        ];
    }

    /**
     * Format error response data.
     *
     * @param \Flugg\Responder\Http\ErrorResponse $response
     * @return array
     */
    public function error(ErrorResponse $response): array
    {
        $error = ['code' => $response->code()];

        if ($message = $response->message()) {
            $error['message'] = $message;
        }

        return array_merge(['error' => $error], $response->meta());
    }

    /**
     * Format a JSON API resource.
     *
     * @param Item $resource
     * @return array
     */
    protected function resource(Item $resource): array
    {
        if (!isset($resource['id'])) {
            throw new InvalidArgumentException('JSON API resource objects must have an ID');
        }

        $data = [
            'type' => $resource->key(),
            'id' => $resource['id'],
            'attributes' => Arr::except($resource->data(), 'id'),
        ];

        if (count($resource->relations())) {
            $data['relationships'] = array_reduce($resource->relations(), function ($previous, $relation) {
                if ($relation instanceof Item) {
                    return array_merge($previous, [
                        $relation->key() => [
                            'data' => [
                                'type' => $relation->key(),
                                'id' => $relation['id'],
                            ],
                        ],
                    ]);
                }
            }, []);
        }

        return $data;
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
            'count' => (int) $paginator->count(),
            'total' => (int) $paginator->total(),
            'per_page' => (int) $paginator->perPage(),
            'current_page' => $currentPage = $paginator->currentPage(),
            'total_pages' => $totalPages = $paginator->lastPage(),
            'links' => [
                'self' => $paginator->url($currentPage),
                'first' => $paginator->url(1),
                'last' => $paginator->url($totalPages),
            ],
        ];

        if ($currentPage > 1) {
            $pagination['links']['prev'] = $paginator->url($currentPage - 1);
        }

        if ($currentPage < $totalPages) {
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
