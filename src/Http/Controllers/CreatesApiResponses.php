<?php

namespace Flugg\Responder\Http\Controllers;

use Flugg\Responder\Http\SuccessResponseBuilder;
use Flugg\Responder\Responder;
use Illuminate\Http\JsonResponse;

/**
 * Use this trait in your base controller for quick access to the responder service
 * in your controller methods.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
trait MakesApiResponses
{
    /**
     * Generate an error JSON response.
     *
     * @param  string|null $errorCode
     * @param  int|null    $statusCode
     * @param  mixed       $message
     * @return JsonResponse
     */
    public function error(string $errorCode = null, int $statusCode = null, $message = null):JsonResponse
    {
        return app(Responder::class)->error($errorCode, $statusCode, $message);
    }

    /**
     * Generate a successful JSON response.
     *
     * @param  mixed|null $data
     * @param  int|null   $statusCode
     * @param  array      $meta
     * @return \Illuminate\Http\JsonResponse
     */
    public function success($data = null, $statusCode = null, array $meta = []):JsonResponse
    {
        return app(Responder::class)->success($data, $statusCode, $meta);
    }

    /**
     * Transform the data and return a success response builder.
     *
     * @param  mixed|null           $data
     * @param  callable|string|null $transformer
     * @return \Flugg\Responder\Http\SuccessResponseBuilder
     */
    public function transform($data = null, $transformer = null):SuccessResponseBuilder
    {
        return app(Responder::class)->transform($data, $transformer);
    }
}