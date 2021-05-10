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
     * @param string|null $resourceKey
     * @throws \Flugg\Responder\Exceptions\InvalidDataException
     * @return \Flugg\Responder\Http\Builders\SuccessResponseBuilder
     */
    public function success($data = null, ?string $resourceKey = null): SuccessResponseBuilder
    {
        return $this->successResponseBuilder->make($data, $resourceKey);
    }

    /**
     * Build an error response.
     *
     * @param \Exception|int|string|null $code
     * @param \Exception|string|null $message
     * @return \Flugg\Responder\Http\Builders\ErrorResponseBuilder
     */
    public function error($code = null, $message = null): ErrorResponseBuilder
    {
        return $this->errorResponseBuilder->make($code, $message);
    }
}
