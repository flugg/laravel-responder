<?php

namespace Flugg\Responder\Contracts\Http;

use Flugg\Responder\Contracts\Pagination\CursorPaginator;
use Flugg\Responder\Contracts\Pagination\Paginator;
use Flugg\Responder\Contracts\Validation\Validator;
use Flugg\Responder\Http\ErrorResponse;
use Flugg\Responder\Http\SuccessResponse;

/**
 * Contract for a response formatter.
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
     * Attach pagination data to the formatted success response data.
     *
     * @param array $data
     * @param Paginator $paginator
     * @return array
     */
    public function paginator(array $data, Paginator $paginator): array;

    /**
     * Attach cursor pagination data to the formatted success response data.
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
     * Attach validation errors to the formatted error response data.
     *
     * @param array $data
     * @param Validator $validator
     * @return array
     */
    public function validator(array $data, Validator $validator): array;
}
