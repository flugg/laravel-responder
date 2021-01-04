<?php

namespace Flugg\Responder;

use Flugg\Responder\Contracts\Responder as ResponderContract;
use Flugg\Responder\Http\Builders\ErrorResponseBuilder;
use Flugg\Responder\Http\Builders\SuccessResponseBuilder;

/**
 * Service class for building success- and error responses.
 */
class Responder implements ResponderContract
{
    /**
     * Builder class for building success responses.
     *
     * @var \Flugg\Responder\Http\Builders\SuccessResponseBuilder
     */
    protected $successResponseBuilder;

    /**
     * Builder class for building error responses.
     *
     * @var \Flugg\Responder\Http\Builders\ErrorResponseBuilder
     */
    protected $errorResponseBuilder;

    /**
     * Create a new service instance.
     *
     * @param \Flugg\Responder\Http\Builders\SuccessResponseBuilder $successResponseBuilder
     * @param \Flugg\Responder\Http\Builders\ErrorResponseBuilder $errorResponseBuilder
     */
    public function __construct(
        SuccessResponseBuilder $successResponseBuilder,
        ErrorResponseBuilder $errorResponseBuilder
    ) {
        $this->successResponseBuilder = $successResponseBuilder;
        $this->errorResponseBuilder = $errorResponseBuilder;
    }

    /**
     * Build a success response.
     *
     * @param mixed $data
     * @return \Flugg\Responder\Http\Builders\SuccessResponseBuilder
     * @throws \Flugg\Responder\Exceptions\InvalidDataException
     */
    public function success($data = []): SuccessResponseBuilder
    {
        return $this->successResponseBuilder->make($data);
    }

    /**
     * Build an error response.
     *
     * @param int|string|\Exception|null $code
     * @param string|\Exception|null $message
     * @return \Flugg\Responder\Http\Builders\ErrorResponseBuilder
     */
    public function error($code = null, $message = null): ErrorResponseBuilder
    {
        return $this->errorResponseBuilder->make($code, $message);
    }
}
