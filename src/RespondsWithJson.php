<?php

namespace Mangopixel\Responder;

use Illuminate\Http\JsonResponse;
use Mangopixel\Responder\Contracts\Responder;

/**
 *
 *
 * @package Laravel Responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
trait RespondsWithJson
{
    /**
     *
     *
     * @param  mixed $data
     * @param  int   $statusCode
     * @return JsonResponse
     */
    public function successResponse( $data, int $statusCode = 200 ):JsonResponse
    {
        return app( Responder::class )->success( $data, $statusCode );
    }

    /**
     *
     *
     * @param  string $error
     * @param  int    $statusCode
     * @return JsonResponse
     */
    public function errorResponse( string $error, int $statusCode = 404 ):JsonResponse
    {
        return app( Responder::class )->error( $error, $statusCode );
    }
}