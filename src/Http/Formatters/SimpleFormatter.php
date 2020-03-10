<?php

namespace Flugg\Responder\Http\Formatters;

use Flugg\Responder\Contracts\Http\ResponseFormatter;
use Flugg\Responder\Contracts\Pagination\CursorPaginator;
use Flugg\Responder\Contracts\Pagination\Paginator;
use Flugg\Responder\Contracts\Validation\Validator;
use Flugg\Responder\Http\ErrorResponse;
use Flugg\Responder\Http\SuccessResponse;

/**
 * A simple response formatter class.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class SimpleFormatter implements ResponseFormatter
{
    /**
     * Format success response data.
     *
     * @param SuccessResponse $response
     * @return array
     */
    public function success(SuccessResponse $response): array
    {
        return array_merge([
            'data' => $response->data(),
        ], $response->meta());
    }

    /**
     * Format success response data with pagination.
     *
     * @param array $data
     * @param Paginator $paginator
     * @return array
     */
    public function paginator(array $data, Paginator $paginator): array
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

        return array_merge($data, ['pagination' => $pagination]);
    }

    /**
     * Format success response data with cursor pagination.
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
        $data = [
            'error' => [
                'code' => $response->errorCode(),
            ],
        ];

        if ($message = $response->message()) {
            $data['error']['message'] = $message;
        }

        return $data;
    }

    /**
     * Format error response data with a validator.
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
}
