<?php

namespace Flugg\Responder\Http;

use Flugg\Responder\Contracts\Http\ErrorMessageResolver as ErrorMessageResolverContract;
use Illuminate\Contracts\Translation\Translator;

/**
 * A class for resolving error messages from error codes.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class ErrorMessageResolver implements ErrorMessageResolverContract
{
    /**
     * A translation service for resolving error messages from language files.
     *
     * @var Translator
     */
    protected $translator;

    /**
     * A list of registered error messages mapped to error codes.
     *
     * @var array
     */
    protected $messages = [];

    /**
     * Create a new error message resolver class.
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
     * @param int|string|array $code
     * @param string|null $message
     * @return void
     */
    public function register($code, string $message = null): void
    {
        $this->messages = array_merge($this->messages, is_array($code) ? $code : [
            $code => $message,
        ]);
    }

    /**
     * Resolve an error message from an error code.
     *
     * @param int|string $code
     * @return string|null
     */
    public function resolve($code): ?string
    {
        if (key_exists($code, $this->messages)) {
            return $this->messages[$code];
        }

        if ($translation = $this->translator->get($code = "errors.$code")) {
            return $translation;
        }

        return null;
    }
}
