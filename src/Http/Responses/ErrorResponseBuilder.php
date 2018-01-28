<?php

namespace Flugg\Responder\Http\Responses;

use Flugg\Responder\Contracts\ErrorFactory;
use Flugg\Responder\Contracts\ErrorSerializer;
use Flugg\Responder\Contracts\ResponseFactory;
use Flugg\Responder\Exceptions\InvalidErrorSerializerException;
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
     * A serializer for formatting error data.
     *
     * @var \Flugg\Responder\Contracts\ErrorSerializer
     */
    protected $serializer;

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
     * @param  mixed|null  $errorCode
     * @param  string|null $message
     * @return $this
     */
    public function error($errorCode = null, string $message = null)
    {
        $this->errorCode = $errorCode;
        $this->message = $message;

        return $this;
    }

    /**
     * Add additional data to the error.
     *
     * @param  array|null $data
     * @return $this
     */
    public function data(array $data = null)
    {
        $this->data = array_merge((array) $this->data, (array) $data);

        return $this;
    }

    /**
     * Set the error serializer.
     *
     * @param  \Flugg\Responder\Contracts\ErrorSerializer|string $serializer
     * @return $this
     * @throws \Flugg\Responder\Exceptions\InvalidErrorSerializerException
     */
    public function serializer($serializer)
    {
        if (is_string($serializer)) {
            $serializer = new $serializer;
        }

        if (! $serializer instanceof ErrorSerializer) {
            throw new InvalidErrorSerializerException;
        }

        $this->serializer = $serializer;

        return $this;
    }

    /**
     * Get the serialized response output.
     *
     * @return array
     */
    protected function getOutput(): array
    {
        return $this->errorFactory->make($this->serializer, $this->errorCode, $this->message, $this->data);
    }

    /**
     * Validate the HTTP status code for the response.
     *
     * @param  int $status
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function validateStatusCode(int $status)
    {
        if ($status < 400 || $status >= 600) {
            throw new InvalidArgumentException("{$status} is not a valid error HTTP status code.");
        }
    }
}
