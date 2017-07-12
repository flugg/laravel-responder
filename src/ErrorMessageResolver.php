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
     * Construct the resolver class.
     *
     * @param \Illuminate\Contracts\Translation\Translator $translator
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Resolve a message from the given error code.
     *
     * @param  string $errorCode
     * @return string|null
     */
    public function resolve(string $errorCode)
    {
        if (! $this->translator->has($errorCode = "errors.$errorCode")) {
            return null;
        }

        return $this->translator->trans($errorCode);
    }
}