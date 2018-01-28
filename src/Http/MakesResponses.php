<?php

namespace Flugg\Responder\Http;

use Flugg\Responder\Http\Responses\ErrorResponseBuilder;
use Flugg\Responder\Http\Responses\SuccessResponseBuilder;
use Flugg\Responder\Responder;

/**
 * A trait to be used by controllers to easily make success- and error responses.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
trait MakesResponses
{
    /**
     * Build a successful response.
     *
     * @param  mixed                                                          $data
     * @param  callable|string|\Flugg\Responder\Transformers\Transformer|null $transformer
     * @param  string|null                                                    $resourceKey
     * @return \Flugg\Responder\Http\Responses\SuccessResponseBuilder
     */
    public function success($data = null, $transformer = null, string $resourceKey = null): SuccessResponseBuilder
    {
        return app(Responder::class)->success(...func_get_args());
    }

    /**
     * Build an error response.
     *
     * @param  mixed|null  $errorCode
     * @param  string|null $message
     * @return \Flugg\Responder\Http\Responses\ErrorResponseBuilder
     */
    public function error($errorCode = null, string $message = null): ErrorResponseBuilder
    {
        return app(Responder::class)->error(...func_get_args());
    }
}