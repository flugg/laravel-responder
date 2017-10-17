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
     * Construct the factory class.
     *
     * @param \Flugg\Responder\Contracts\ErrorMessageResolver $messageResolver
     */
    public function __construct(ErrorMessageResolverContract $messageResolver)
    {
        $this->messageResolver = $messageResolver;
    }

    /**
     * Make an error array from the given error code and message.
     *
     * @param  \Flugg\Responder\Contracts\ErrorSerializer $serializer
     * @param  mixed|null                                 $errorCode
     * @param  string|null                                $message
     * @param  array|null                                 $data
     * @return array
     */
    public function make(ErrorSerializer $serializer, $errorCode = null, string $message = null, array $data = null): array
    {
        if (isset($errorCode) && ! isset($message)) {
            $message = $this->messageResolver->resolve($errorCode);
        }

        return $serializer->format($errorCode, $message, $data);
    }
}