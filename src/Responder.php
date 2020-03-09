<?php

namespace Flugg\Responder;

use Exception;
use Flugg\Responder\Contracts\Http\ErrorResponseBuilder;
use Flugg\Responder\Contracts\Http\SuccessResponseBuilder;
use Flugg\Responder\Contracts\Responder as ResponderContract;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * A service class responsible for building responses.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class Responder implements ResponderContract
{
    /**
     * A builder for building success responses.
     *
     * @var SuccessResponseBuilder
     */
    protected $successResponseBuilder;

    /**
     * A builder for building error responses.
     *
     * @var ErrorResponseBuilder
     */
    protected $errorResponseBuilder;

    /**
     * Constructs the class.
     *
     * @param SuccessResponseBuilder $successResponseBuilder¨
     * @param ErrorResponseBuilder $errorResponseBuilder
     */
    public function __construct(SuccessResponseBuilder $successResponseBuilder, ErrorResponseBuilder $errorResponseBuilder)
    {
        $this->successResponseBuilder = $successResponseBuilder;
        $this->errorResponseBuilder = $errorResponseBuilder;
    }

    /**
     * Build a success response.
     *
     * @param array|Arrayable|Builder|QueryBuilder|Relation $data
     * @return SuccessResponseBuilder
     */
    public function success($data = null): SuccessResponseBuilder
    {
        return $this->successResponseBuilder->data($data);
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
        return $this->errorResponseBuilder->error($errorCode, $message);
    }
}
