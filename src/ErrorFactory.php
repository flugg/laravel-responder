<?php

namespace Flugg\Responder;

use Flugg\Responder\Contracts\ErrorFactory as ErrorFactoryContract;
use Flugg\Responder\Contracts\ErrorMessageResolver as ErrorMessageResolverContract;
use Flugg\Responder\Contracts\ErrorSerializer;

/**
 * A factory class responsible for creating error arrays.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class ErrorFactory implements ErrorFactoryContract
{
    /**
     * A resolver for resolving messages from error codes.
     *
     * @var \Flugg\Responder\Contracts\ErrorMessageResolver
     */
    protected $messageResolver;

    /**
     * A serializer for formatting errors.
     *
     * @var \Flugg\Responder\Contracts\ErrorSerializer
     */
    protected $serializer;

    /**
     * Construct the factory class.
     *
     * @param \Flugg\Responder\Contracts\ErrorMessageResolver $messageResolver
     * @param \Flugg\Responder\Contracts\ErrorSerializer      $serializer
     */
    public function __construct(ErrorMessageResolverContract $messageResolver, ErrorSerializer $serializer)
    {
        $this->messageResolver = $messageResolver;
        $this->serializer = $serializer;
    }

    /**
     * Make an error array from the given error code and message.
     *
     * @param  string|null $errorCode
     * @param  string|null $message
     * @param  array|null  $data
     * @return array
     */
    public function make(string $errorCode = null, string $message = null, array $data = null): array
    {
        if (isset($errorCode) && ! isset($message)) {
            $message = $this->messageResolver->resolve($errorCode);
        }

        return $this->serializer->format($errorCode, $message, $data);
    }
}