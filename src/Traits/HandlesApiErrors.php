<?php

namespace Flugg\Responder\Traits;

use Exception;
use Flugg\Responder\Exceptions\Http\ApiException;
use Flugg\Responder\Exceptions\Http\ResourceNotFoundException;
use Flugg\Responder\Exceptions\Http\UnauthenticatedException;
use Flugg\Responder\Exceptions\Http\UnauthorizedException;
use Flugg\Responder\Exceptions\Http\ValidationFailedException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * Use this trait in your exceptions handler to give you access to methods you may
 * use to let the package catch and handle any API exceptions.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
trait HandlesApiErrors
{
    /**
     * Transform Laravel exceptions into API exceptions.
     *
     * @param  Exception $exception
     * @return void
     * @throws UnauthenticatedException
     * @throws UnauthorizedException
     * @throws ResourceNotFoundException
     * @throws RelationNotFoundException
     * @throws ValidationFailedException
     */
    protected function transformException(Exception $exception)
    {
        if ($exception instanceof AuthenticationException) {
            throw new UnauthenticatedException();
        }

        if ($exception instanceof AuthorizationException) {
            throw new UnauthorizedException();
        }

        if ($exception instanceof ModelNotFoundException) {
            throw new ResourceNotFoundException();
        }

        if ($exception instanceof RelationNotFoundException) {
            throw new RelationNotFoundException();
        }

        if ($exception instanceof ValidationException) {
            throw new ValidationFailedException($exception->validator);
        }
    }

    /**
     * Renders any API exception into a JSON error response.
     *
     * @param  ApiException $exception
     * @return JsonResponse
     */
    protected function renderApiError(ApiException $exception):JsonResponse
    {
        return app('responder.error')
            ->setError($exception->getErrorCode(), $exception->getMessage())
            ->addData($exception->getData())
            ->respond($exception->getStatusCode());
    }
}