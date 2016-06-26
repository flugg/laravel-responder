<?php

namespace Mangopixel\Responder\Contracts;

use Illuminate\Http\JsonResponse;

/**
 *
 *
 * @package Laravel Responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
interface Respondable
{
    /**
     *
     *
     * @param  mixed $data
     * @param  int   $statusCode
     * @return JsonResponse
     */
    public function success( $data, int $statusCode ):JsonResponse;

    /**
     *
     *
     * @param  string $error
     * @param  int    $statusCode
     * @return JsonResponse
     */
    public function error( string $error, int $statusCode ):JsonResponse;
}