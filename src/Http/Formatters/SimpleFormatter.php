<?php

namespace Flugg\Responder\Http\Formatters;

use Flugg\Responder\Contracts\Http\ResponseFormatter;
use Flugg\Responder\Contracts\Pagination\CursorPaginator;
use Flugg\Responder\Contracts\Pagination\Paginator;
use Flugg\Responder\Contracts\Validation\Validator;
use Flugg\Responder\Http\ErrorResponse;
use Flugg\Responder\Http\SuccessResponse;

/**
 * Simple response formatter.
 */
class SimpleFormatter implements ResponseFormatter
{
    /**
     * Format success response data.
     *
     * @param \Flugg\Responder\Http\SuccessResponse $response
     * @return array
     */
    public function success(SuccessResponse $response): array
    {
        $data = array_merge([
            'data' => $response->resource()->data(),
        ], $response->meta());

        if ($paginator = $response->paginator()) {
            $data['pagination'] = $this->paginator($paginator);
        } elseif ($paginator = $response->cursorPaginator()) {
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
        $data = array_merge([
            'error' => [
                'code' => $response->code(),
            ]
        ], $response->meta());

        if ($message = $response->message()) {
            $data['error']['message'] = $message;
        }

        if ($validator = $response->validator()) {
            $data['error'] = array_merge($data['error'], $this->validator($validator));
        }

        return $data;
    }

    /**
     * Format pagination meta data.
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
     * Format cursor pagination meta data.
     *
     * @param \Flugg\Responder\Contracts\Pagination\CursorPaginator $paginator
     * @return array
     */
    protected function cursor(CursorPaginator $paginator): array
    {
        return  [
            'current' => $paginator->current(),
            'previous' => $paginator->previous(),
            'next' => $paginator->next(),
            'count' => $paginator->count(),
        ];
    }

    /**
     * Format validator meta data.
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
