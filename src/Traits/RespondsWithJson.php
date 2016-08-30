<?php

namespace Flugg\Responder\Traits;

use Flugg\Responder\Http\SuccessResponseBuilder;
use Flugg\Responder\Responder;
use Illuminate\Http\JsonResponse;

/**
 * Use this trait in your base controllere for quick access to the responder service
 * methods in your controllers.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
trait RespondsWithJson
{
    /**
     * Generate an error JSON response.
     *
     * @param  string $error
     * @param  int    $statusCode
     * @param  mixed  $message
     * @return JsonResponse
     */
    public function errorResponse(string $error = null, int $statusCode = null, $message = null):JsonResponse
    {
        return app(Responder::class)->error($error, $statusCode, $message);
    }

    /**
     * Generate a successful JSON response.
     *
     * @param  mixed|null $data
     * @param  int|null   $statusCode
     * @param  array      $meta
     * @return \Illuminate\Http\JsonResponse
     */
    public function successResponse($data = null, $statusCode = null, array $meta = []):JsonResponse
    {
        return app(Responder::class)->success($data, $statusCode, $meta);
    }

    /**
     * Transform the data and return a success response builder.
     *
     * @param  mixed|null           $data
     * @param  callable|string|null $transformer
     * @return \Flugg\Responder\Http\SuccessResponse
     */
    public function transform($data = null, $transformer = null):SuccessResponseBuilder
    {
        return app(Responder::class)->transform($data, $transformer);
    }
}