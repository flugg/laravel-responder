<?php

namespace Flugg\Responder;

use Flugg\Responder\Contracts\ErrorMessageRegistry as ErrorMessageRegistryContract;

/**
 * Class for registering and resolving error messages from error codes.
 */
class ErrorMessageRegistry implements ErrorMessageRegistryContract
{
    /**
     * List of registered error messages mapped to error codes.
     *
     * @var array
     */
    protected $messages = [];

    /**
     * Register error messages mapped to error codes.
     *
     * @param array|int|string $code
     * @param string|null $message
     * @return void
     */
    public function register($code, ?string $message = null): void
    {
        $this->messages = array_merge($this->messages, is_array($code) ? $code : [$code => $message]);
    }

    /**
     * Resolve an error message from an error code.
     *
     * @param int|string $code
     * @return string|null
     */
    public function resolve($code): ?string
    {
        return $this->messages[$code] ?? null;
    }
}
