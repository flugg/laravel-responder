<?php

namespace Flugg\Responder\Http\Formatters;

use Flugg\Responder\Contracts\Http\ResponseFormatter;
use Flugg\Responder\Contracts\Pagination\CursorPaginator;
use Flugg\Responder\Contracts\Pagination\Paginator;
use Flugg\Responder\Contracts\Validation\Validator;
use Flugg\Responder\Http\ErrorResponse;
use Flugg\Responder\Http\SuccessResponse;
use Illuminate\Support\Arr;
use InvalidArgumentException;

/**
 * Response formatter following the JSON API specification.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class JsonApiFormatter implements ResponseFormatter
{
    /**
     * Format success response data.
     *
     * @param SuccessResponse $response
     * @return array
     */
    public function success(SuccessResponse $response): array
    {
        if (Arr::isAssoc($response->data())) {
            return [
                'data' => $this->formatResource($response->data())
            ];
        }

        return [
            'data' => array_map(function ($resource) {
                return $this->formatResource($resource);
            }, $response->data())
        ];
    }

    /**
     * Attach pagination data to the formatted success response data.
     *
     * @param array $data
     * @param Paginator $paginator
     * @return array
     */
    public function paginator(array $data, Paginator $paginator): array
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

        return array_merge($data, ['pagination' => $pagination]);
    }

    /**
     * Attach cursor pagination data to the formatted success response data.
     *
     * @param array $data
     * @param CursorPaginator $paginator
     * @return array
     */
    public function cursor(array $data, CursorPaginator $paginator): array
    {
        return array_merge($data, [
            'cursor' => [
                'current' => $paginator->current(),
                'previous' => $paginator->previous(),
                'next' => $paginator->next(),
                'count' => $paginator->count(),
            ],
        ]);
    }

    /**
     * Format error response data.
     *
     * @param ErrorResponse $response
     * @return array
     */
    public function error(ErrorResponse $response): array
    {
        $error = [
            'code' => $response->code(),
        ];

        if ($message = $response->message()) {
            $error['message'] = $message;
        }

        return array_merge([
            'error' => $error,
        ], $response->meta());
    }

    /**
     * Attach validation errors to the formatted error response data.
     *
     * @param array $data
     * @param Validator $validator
     * @return array
     */
    public function validator(array $data, Validator $validator): array
    {
        return [
            'error' => array_merge($data['error'], [
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
            ]),
        ];
    }

    protected function formatResource(array $data): array
    {
        if (!array_key_exists('id', $data)) {
            throw new InvalidArgumentException('JSON API resource objects must have an ID');
        }

        $resource = [
            'type' => 'RESOURCE_KEY',
            'id' => $data['id'],
            'attributes' => Arr::except($data, 'id'),
        ];

        return $resource;
    }
}
