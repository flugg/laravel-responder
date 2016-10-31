<?php

namespace Flugg\Responder\Http;

use InvalidArgumentException;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * This class represents an error response. An error response is responsible for translating
 * and resolving messages from error code and turning them into an error JSON response.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class ErrorResponseBuilder extends ResponseBuilder
{
    /**
     * Optional error data appended with the response.
     *
     * @var array
     */
    protected $data = [];

    /**
     * The error code used to identify the error.
     *
     * @var string
     */
    protected $errorCode;

    /**
     * A descriptive error message explaining what went wrong.
     *
     * @var string
     */
    protected $message;

    /**
     * Any parameters used to build the error message.
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * The HTTP status code for the response.
     *
     * @var int
     */
    protected $statusCode = 500;

    /**
     * Translator service used for translating stuff.
     *
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    protected $translator;

    /**
     * Constructor.
     *
     * @param \Illuminate\Contracts\Routing\ResponseFactory|\Laravel\Lumen\Http\ResponseFactory $responseFactory
     * @param \Symfony\Component\Translation\TranslatorInterface                                $translator
     */
    public function __construct($responseFactory, TranslatorInterface $translator)
    {
        $this->translator = $translator;

        parent::__construct($responseFactory);
    }

    /**
     * Add additonal data appended to the error object.
     *
     * @param  array $data
     * @return self
     */
    public function addData(array $data):ErrorResponseBuilder
    {
        $this->data = array_merge($this->data, $data);

        return $this;
    }

    /**
     * Set the error code and optionally an error message.
     *
     * @param  string|null       $errorCode
     * @param  string|array|null $message
     * @return self
     */
    public function setError(string $errorCode = null, $message = null):ErrorResponseBuilder
    {
        $this->errorCode = $errorCode;

        if (is_array($message)) {
            $this->parameters = $message;
        } else {
            $this->message = $message;
        }

        return $this;
    }

    /**
     * Set the HTTP status code for the response.
     *
     * @param  int $statusCode
     * @return self
     * @throws \InvalidArgumentException
     */
    public function setStatus(int $statusCode):ResponseBuilder
    {
        if ($statusCode < 400 || $statusCode >= 600) {
            throw new InvalidArgumentException("{$statusCode} is not a valid error HTTP status code.");
        }

        return parent::setStatus($statusCode);
    }

    /**
     * Serialize the data and return as an array.
     *
     * @return array
     */
    public function toArray():array
    {
        return [
            'success' => false,
            'error' => $this->buildErrorData()
        ];
    }

    /**
     * Build the error object of the serialized response data.
     *
     * @return array|null
     */
    protected function buildErrorData()
    {
        if (is_null($this->errorCode)) {
            return null;
        }

        $data = [
            'code' => $this->errorCode,
            'message' => $this->message ?: $this->resolveMessage()
        ];

        return array_merge($data, $this->data);
    }

    /**
     * Resolve an error message from the translator.
     *
     * @return string|null
     */
    protected function resolveMessage()
    {
        if (! $this->translator->has($code = "errors.$this->errorCode")) {
            return null;
        }

        return $this->translator->trans($code, $this->parameters);
    }
}