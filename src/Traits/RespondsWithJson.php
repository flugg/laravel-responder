<?php

namespace Mangopixel\Responder\Traits;

use Illuminate\Http\JsonResponse;
use Mangopixel\Responder\Contracts\Responder;

/**
 * A trait you may apply to your controllers for quick access to the responder.
 *
 * @package Laravel Responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
trait RespondsWithJson
{
    /**
     * Generate a successful JSON response.
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
     * Generate an error JSON response.
     *
     * @param  string $error
     * @param  int    $statusCode
     * @param  string $message
     * @return JsonResponse
     */
    public function errorResponse( string $error, int $statusCode = 404, $message = null ):JsonResponse
    {
        return app( Responder::class )->error( $error, $statusCode, $message );
    }
}