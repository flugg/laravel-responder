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
 * A trait for converting exceptions to JSON responses in exception handler classes.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
trait ConvertsExceptions
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
        foreach (config('responder.exceptions') as $class => $error) {
            if ($exception instanceof $class) {
                return !(config('app.debug') && $error['status'] >= 500);
            }
        }

        return false;
    }
}
