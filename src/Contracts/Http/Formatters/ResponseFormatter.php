<?php

namespace Flugg\Responder\Contracts\Http\Formatters;

use Flugg\Responder\Contracts\Pagination\CursorPaginator;
use Flugg\Responder\Contracts\Pagination\Paginator;
use Flugg\Responder\Contracts\Validation\Validator;
use Flugg\Responder\Http\ErrorResponse;
use Flugg\Responder\Http\SuccessResponse;

/**
 * A contract for a response formatter.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
interface ResponseFormatter
{
    /**
     * Format success response data.
     *
     * @param SuccessResponse $response
     * @return array
     */
    public function success(SuccessResponse $response): array;

    /**
     * Format success response data with pagination.
     *
     * @param array $data
     * @param Paginator $paginator
     * @return array
     */
    public function paginator(array $data, Paginator $paginator): array;

    /**
     * Format success response data with cursor pagination.
     *
     * @param array $data
     * @param CursorPaginator $paginator
     * @return array
     */
    public function cursor(array $data, CursorPaginator $paginator): array;

    /**
     * Format error response data.
     *
     * @param ErrorResponse $response
     * @return array
     */
    public function error(ErrorResponse $response): array;

    /**
     * Format error response data with a validator.
     *
     * @param array $data
     * @param Validator $validator
     * @return array
     */
    public function validator(array $data, Validator $validator): array;
}
