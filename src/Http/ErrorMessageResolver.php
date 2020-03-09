<?php

namespace Flugg\Responder\Http;

use Flugg\Responder\Contracts\Http\ErrorMessageResolver as ErrorMessageResolverContract;
use Illuminate\Contracts\Translation\Translator;

/**
 * A resolver class for resolving error messages from error codes.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class ErrorMessageResolver implements ErrorMessageResolverContract
{
    /**
     * A translation service for resolving messages from language files.
     *
     * @var Translator
     */
    protected $translator;

    /**
     * A list of registered messages mapped to error codes.
     *
     * @var array
     */
    protected $messages = [];

    /**
     * Construct the class.
     *
     * @param Translator $translator
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Register error messages mapped to error codes.
     *
     * @param array|int|string $errorCode
     * @param string|null $message
     * @return void
     */
    public function register($errorCode, string $message = null): void
    {
        $this->messages = array_merge($this->messages, is_array($errorCode) ? $errorCode : [
            $errorCode => $message,
        ]);
    }

    /**
     * Resolve a message from the given error code.
     *
     * @param int|string $errorCode
     * @return string|null
     */
    public function resolve($errorCode): ?string
    {
        if (key_exists($errorCode, $this->messages)) {
            return $this->messages[$errorCode];
        }

        if ($translation = $this->translator->get($errorCode = "errors.$errorCode")) {
            return $translation;
        }

        return null;
    }
}
