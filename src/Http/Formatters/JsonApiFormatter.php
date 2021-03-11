<?php

namespace Flugg\Responder\Http\Formatters;

use Flugg\Responder\Contracts\Http\Formatter;
use Flugg\Responder\Contracts\Pagination\CursorPaginator;
use Flugg\Responder\Contracts\Pagination\Paginator;
use Flugg\Responder\Contracts\Validation\Validator;
use Flugg\Responder\Http\ErrorResponse;
use Flugg\Responder\Http\Resources\Collection;
use Flugg\Responder\Http\Resources\Item;
use Flugg\Responder\Http\Resources\Resource;
use Flugg\Responder\Http\SuccessResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection as IlluminateCollection;
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
        $data = ['data' => $this->data($response->resource())];

        if ($included = $this->included($response->resource())) {
            $data['included'] = $included;
        }

        if ($meta = $response->meta()) {
            $data['meta'] = $meta;
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
        $error = ['code' => $response->code()];

        if ($message = $response->message()) {
            $error['message'] = $message;
        }

        return array_merge(['error' => $error], $response->meta());
    }

    /**
     * Format success data structure from a resource.
     *
     * @param \Flugg\Responder\Http\Resources\Resource $resource
     * @return array
     */
    protected function data(Resource $resource): array
    {
        if ($resource instanceof Item) {
            return $this->resource($resource);
        } elseif ($resource instanceof Collection) {
            return array_map([$this, 'resource'], $resource->items());
        }

        throw new InvalidArgumentException("Unsupported resource class [{get_class($resource)}]");
    }

    /**
     * Format included resources.
     *
     * @param \Flugg\Responder\Http\Resources\Resource $resource
     * @return array
     */
    protected function included(Resource $resource): array
    {
        $included = [];
        $resources = $resource instanceof Item ? $resource->relations() :
            ($resource instanceof Collection ? $resource->items() : []);

        foreach ($resources as $relation) {
            $included = array_merge(
                $included,
                $relation instanceof Item ? [$this->resource($relation)] : [],
                $this->included($relation)
            );
        }

        return $included;
    }

    /**
     * Format a JSON API resource object.
     *
     * @param Item $resource
     * @return array
     */
    protected function resource(Item $resource): array
    {
        $identifier = $this->resourceIdentifier($resource);
        $identifier['data'] = Arr::except($resource->data(), 'id');

        if (count($resource->relations())) {
            $identifier['relationships'] = $this->relationships($resource);
        }

        return $identifier;
    }

    /**
     * Format a JSON API resource identifier object.
     *
     * @param Item $resource
     * @return array
     */
    protected function resourceIdentifier(Item $resource): array
    {
        if (! isset($resource->id)) {
            throw new InvalidArgumentException('JSON API resource objects must have an ID');
        }

        return [
            'type' => $resource->key(),
            'id' => $resource->id,
        ];
    }

    /**
     * Format relationship data from a resource.
     *
     * @param \Flugg\Responder\Http\Resources\Resource $resource
     * @return array
     */
    protected function relationships(Item $resource): array
    {
        return IlluminateCollection::make($resource->relations())
            ->mapWithKeys(function ($value, $key) {
                if ($value instanceof Item) {
                    return [$key => ['data' => $this->relationships($value)]];
                } elseif ($value instanceof Collection) {
                    return array_map([$this, 'resourceIdentifier'], $value->items());
                } else {
                    throw new InvalidArgumentException("Unsupported nested resource class [{get_class($value)}]");
                }
            })->toArray();
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
