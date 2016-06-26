<?php

namespace Mangopixel\Responder\Contracts;

use Illuminate\Http\JsonResponse;

/**
 * A responder contract for the responder service which handles the generation of
 * API responses. You may inject this class into your controllers to generate
 * responses directly through the responder service.
 *
 * @package Laravel Responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
interface Responder
{
    /**
     * Generate a successful JSON response.
     *
     * @param  mixed $data
     * @param  int   $statusCode
     * @return JsonResponse
     */
    public function success( $data, int $statusCode = 200 ):JsonResponse;

    /**
     * Generate an error JSON response.
     *
     * @param  string $error
     * @param  int    $statusCode
     * @return JsonResponse
     */
    public function error( string $error, int $statusCode = 404 ):JsonResponse;
}