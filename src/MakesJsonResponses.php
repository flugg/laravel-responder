<?php

namespace Flugg\Responder;

use Exception;
use Flugg\Responder\Contracts\Http\ErrorResponseBuilder;
use Flugg\Responder\Contracts\Http\SuccessResponseBuilder;
use Flugg\Responder\Contracts\Responder;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * A trait to be used to easily make success- and error responses.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
trait MakesJsonResponses
{
    /**
     * Build a success response.
     *
     * @param array|Arrayable|Builder|QueryBuilder|Relation $data
     * @return SuccessResponseBuilder
     */
    public function success($data = null): SuccessResponseBuilder
    {
        return app(Responder::class)->success($data);
    }

    /**
     * Build an error response.
     *
     * @param Exception|int|string|null $errorCode
     * @param Exception|string|null $message
     * @return ErrorResponseBuilder
     */
    public function error($errorCode = null, $message = null): ErrorResponseBuilder
    {
        return app(Responder::class)->error($errorCode, $message);
    }
}
