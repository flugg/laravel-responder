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
        $data = ['errors' => ($validator = $response->validator())
            ? $this->validator($validator, $response)
            : [$this->errorObject($response)],
        ];

        if ($meta = $response->meta()) {
            $data['meta'] = $meta;
        }

        return $data;
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

        throw new InvalidArgumentException(sprintf('Unsupported resource class [%s]', get_class($resource)));
    }

    /**
     * Format a JSON API resource object.
     *
     * @param \Flugg\Responder\Http\Resources\Item $resource
     * @return array
     */
    protected function resource(Item $resource): array
    {
        $identifier = $this->resourceIdentifier($resource);
        $identifier['attributes'] = Arr::except($resource->data(), 'id');

        if (count($resource->relations())) {
            $identifier['relationships'] = $this->relationships($resource);
        }

        return $identifier;
    }

    /**
     * Format a JSON API resource identifier object.
     *
     * @param \Flugg\Responder\Http\Resources\Item $resource
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
        return array_map(function (Resource $relation) {
            if ($relation instanceof Item) {
                return ['data' => $this->resourceIdentifier($relation)];
            } elseif ($relation instanceof Collection) {
                return ['data' => array_map([$this, 'resourceIdentifier'], $relation->items())];
            } else {
                throw new InvalidArgumentException(sprintf('Unsupported nested resource class [%s]', get_class($relation)));
            }
        }, $resource->relations());
    }

    /**
     * Format included resources.
     *
     * @param \Flugg\Responder\Http\Resources\Resource $resource
     * @param array $included
     * @return array
     */
    protected function included(Resource $resource, array $included = []): array
    {
        if ($resource instanceof Item) {
            return $this->extractRelations($resource, $included);
        } else {
            return $this->extractItems($resource, $included);
        }
    }

    /**
     * Extract related resources from the resource.
     *
     * @param \Flugg\Responder\Http\Resources\Item $resource
     * @param array $included
     * @return array
     */
    protected function extractRelations(Item $resource, array $included): array
    {
        return array_reduce($resource->relations(), function ($previous, $relation) {
            return array_merge(
                $relation instanceof Item ? [$this->resource($relation)] : [],
                $this->included($relation, $previous),
            );
        }, $included);
    }

    /**
     * Extract resources from the resource collection.
     *
     * @param \Flugg\Responder\Http\Resources\Collection $collection
     * @param array $included
     * @return array
     */
    protected function extractItems(Collection $collection, array $included): array
    {
        return array_reduce($collection->items(), function ($previous, $item) use ($included) {
            return array_merge(
                $this->included($item, $previous),
                ! empty($included) ? [$this->resource($item)] : []
            );
        }, $included);
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
     * Format an error object.
     *
     * @param \Flugg\Responder\Http\ErrorResponse $response
     * @return array
     */
    protected function errorObject(ErrorResponse $response): array
    {
        $error = ['code' => $response->code()];

        if ($message = $response->message()) {
            $error['title'] = $message;
        }

        return $error;
    }

    /**
     * Format validator metadata.
     *
     * @param \Flugg\Responder\Contracts\Validation\Validator $validator
     * @param \Flugg\Responder\Http\ErrorResponse $response
     * @return array
     */
    protected function validator(Validator $validator, ErrorResponse $response): array
    {
        return array_reduce($validator->failed(), function ($errors, $field) use ($validator, $response) {
            return array_merge(
                $errors,
                array_map(function ($rule) use ($response, $field, $validator) {
                    return array_merge($this->errorObject($response), [
                        'detail' => $validator->messages()["$field.$rule"],
                        'source' => $field,
                    ]);
                }, $validator->errors()[$field]),
            );
        }, []);
    }
}
