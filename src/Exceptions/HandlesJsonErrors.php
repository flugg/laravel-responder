<?php

namespace Flugg\Responder\Exceptions;

use Exception;
use Flugg\Responder\Contracts\Responder;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

/**
 * An exception handler responsible for converting exceptions to JSON errors.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
trait HandlesJsonErrors
{
    /**
     * Prepare a JSON response for the given exception.
     *
     * @param Request $request
     * @param Exception $exception
     * @return JsonResponse
     */
    protected function prepareJsonResponse($request, Exception $exception)
    {
        if ($this->shouldConvertException($exception)) {
            return app(Responder::class)->error($exception)->respond();
        }

        if (!config('app.debug') && $error = config('responder.fallback_error')) {
            return app(Responder::class)->error($error['code'])->respond($error['status']);
        }

        return parent::prepareJsonResponse($request, $exception);
    }

    /**
     * Convert an authentication exception into a response.
     *
     * @param Request $request
     * @param AuthenticationException $exception
     * @return JsonResponse|Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson() && $this->shouldConvertException($exception)) {
            return app(Responder::class)->error($exception)->respond();
        }

        return parent::unauthenticated($request, $exception);
    }

    /**
     * Convert a validation exception into a JSON response.
     *
     * @param Request $request
     * @param ValidationException $exception
     * @return JsonResponse
     */
    protected function invalidJson($request, ValidationException $exception)
    {
        if ($this->shouldConvertException($exception)) {
            return app(Responder::class)->error($exception)->validator($exception->validator)->respond();
        }

        return parent::invalidJson($request, $exception);
    }

    /**
     * Check if the exception should be converted to an error response.
     *
     * @param Exception $exception
     * @return bool
     */
    protected function shouldConvertException(Exception $exception): bool
    {
        return key_exists(get_class($exception), config('responder.exceptions'));
    }
}
