<?php

namespace Flugg\Responder;

use Flugg\Responder\Contracts\ErrorMessageResolver as ErrorMessageResolverContract;
use Illuminate\Contracts\Translation\Translator;

/**
 * A resolver class responsible for resolving messages from error codes.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class ErrorMessageResolver implements ErrorMessageResolverContract
{
    /**
     * A serivce for resolving messages from language files.
     *
     * @var \Illuminate\Contracts\Translation\Translator
     */
    protected $translator;

    /**
     * A list of registered messages mapped to error codes.
     *
     * @var array
     */
    protected $messages = [];

    /**
     * Construct the resolver class.
     *
     * @param \Illuminate\Contracts\Translation\Translator $translator
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Register a message mapped to an error code.
     *
     * @param  string $errorCode
     * @param  string $message
     * @return void
     */
    public function register(string $errorCode, string $message)
    {
        $this->messages = array_merge($this->messages, is_array($errorCode) ? $errorCode : [
            $errorCode => $message,
        ]);
    }

    /**
     * Resolve a message from the given error code.
     *
     * @param  string $errorCode
     * @return string|null
     */
    public function resolve(string $errorCode)
    {
        if (key_exists($errorCode, $this->messages)) {
            return $this->messages[$errorCode];
        }

        if ($this->translator->has($errorCode = "errors.$errorCode")) {
            return $this->translator->trans($errorCode);
        }

        return null;
    }
}