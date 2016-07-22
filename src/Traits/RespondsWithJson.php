<?php

namespace Flugg\Responder\Traits;

use Flugg\Responder\Contracts\Responder;
use Illuminate\Http\JsonResponse;

/**
 * Use this trait in your base controllere for quick access to the responder service
 * methods in your controllers.
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
     * @param  mixed $statusCode
     * @param  array $meta
     * @return JsonResponse
     */
    public function successResponse( $data = null, $statusCode = 200, array $meta = [ ] ):JsonResponse
    {
        return app( Responder::class )->success( $data, $statusCode, $meta );
    }

    /**
     * Generate an error JSON response.
     *
     * @param  string $error
     * @param  int    $statusCode
     * @param  mixed  $message
     * @return JsonResponse
     */
    public function errorResponse( string $error, int $statusCode = 404, $message = null ):JsonResponse
    {
        return app( Responder::class )->error( $error, $statusCode, $message );
    }
}