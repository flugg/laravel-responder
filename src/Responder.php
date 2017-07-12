<?php

namespace Flugg\Responder;

use Flugg\Responder\Contracts\Responder as ResponderContract;
use Flugg\Responder\Http\Responses\ErrorResponseBuilder;
use Flugg\Responder\Http\Responses\SuccessResponseBuilder;

/**
 * A service class responsible for building responses.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class Responder implements ResponderContract
{
    /**
     * A builder for building error responses.
     *
     * @var \Flugg\Responder\Http\Responses\ErrorResponseBuilder
     */
    protected $errorResponseBuilder;

    /**
     * A builder for building success responses.
     *
     * @var \Flugg\Responder\Http\Responses\SuccessResponseBuilder
     */
    protected $successResponseBuilder;

    /**
     * Construct the service class.
     *
     * @param \Flugg\Responder\Http\Responses\ErrorResponseBuilder   $errorResponseBuilder
     * @param \Flugg\Responder\Http\Responses\SuccessResponseBuilder $successResponseBuilder
     */
    public function __construct(ErrorResponseBuilder $errorResponseBuilder, SuccessResponseBuilder $successResponseBuilder)
    {
        $this->errorResponseBuilder = $errorResponseBuilder;
        $this->successResponseBuilder = $successResponseBuilder;
    }

    /**
     * Build an error response.
     *
     * @param  string|null $errorCode
     * @param  string|null $message
     * @return \Flugg\Responder\Http\Responses\ErrorResponseBuilder
     */
    public function error(string $errorCode = null, string $message = null): ErrorResponseBuilder
    {
        return $this->errorResponseBuilder->error($errorCode, $message);
    }

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
        return $this->successResponseBuilder->transform($data, $transformer, $resourceKey);
    }
}