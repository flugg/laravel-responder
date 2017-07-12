<?php

namespace Flugg\Responder\Http\Responses;

use Flugg\Responder\Contracts\ErrorFactory;
use Flugg\Responder\Contracts\ResponseFactory;
use InvalidArgumentException;

/**
 * A builder class for building error responses.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class ErrorResponseBuilder extends ResponseBuilder
{
    /**
     * A factory for building error data output.
     *
     * @var \Flugg\Responder\Contracts\ErrorFactory
     */
    private $errorFactory;

    /**
     * A code representing the error.
     *
     * @var string|null
     */
    protected $errorCode = null;

    /**
     * A message descibing the error.
     *
     * @var string|null
     */
    protected $message = null;

    /**
     * Additional data included with the error.
     *
     * @var array|null
     */
    protected $data = null;

    /**
     * A HTTP status code for the response.
     *
     * @var int
     */
    protected $status = 500;

    /**
     * Construct the builder class.
     *
     * @param \Flugg\Responder\Contracts\ResponseFactory $responseFactory
     * @param \Flugg\Responder\Contracts\ErrorFactory    $errorFactory
     */
    public function __construct(ResponseFactory $responseFactory, ErrorFactory $errorFactory)
    {
        $this->errorFactory = $errorFactory;

        parent::__construct($responseFactory);
    }

    /**
     * Set the error code and message.
     *
     * @param  string|null $errorCode
     * @param  string|null $message
     * @return self
     */
    public function error(string $errorCode = null, string $message = null): ErrorResponseBuilder
    {
        $this->errorCode = $errorCode;
        $this->message = $message;

        return $this;
    }

    /**
     * Add additional data to the error.
     *
     * @param  array $data
     * @return self
     */
    public function addData(array $data): ErrorResponseBuilder
    {
        $this->data = array_merge((array) $this->data, $data);

        return $this;
    }

    /**
     * Get the serialized response output.
     *
     * @return array
     */
    protected function getOutput(): array
    {
        return $this->errorFactory->make($this->errorCode, $this->message, $this->data);
    }

    /**
     * Validate the HTTP status code for the response.
     *
     * @param  int $status
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function validateStatusCode(int $status): void
    {
        if ($status < 400 || $status >= 600) {
            throw new InvalidArgumentException("{$status} is not a valid error HTTP status code.");
        }
    }
}