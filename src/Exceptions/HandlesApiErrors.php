<?php

namespace Flugg\Responder\Exceptions;

use Exception;
use Flugg\Responder\Exceptions\Http\ApiException;
use Flugg\Responder\Exceptions\Http\RelationNotFoundException;
use Flugg\Responder\Exceptions\Http\ResourceNotFoundException;
use Flugg\Responder\Exceptions\Http\UnauthenticatedException;
use Flugg\Responder\Exceptions\Http\UnauthorizedException;
use Flugg\Responder\Exceptions\Http\ValidationFailedException;
use Flugg\Responder\Http\Responses\ErrorResponseBuilder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\RelationNotFoundException as BaseRelationNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * A trait to be used by an exception handler to handle automatic handling of error responses.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
trait HandlesApiErrors
{
    /**
     * Convert a Laravel exception to an API exception.
     *
     * @param  \Exception $exception
     * @return void
     */
    protected function transformException(Exception $exception)
    {
        $this->transformAuthException($exception);
        $this->transformEloquentException($exception);
        $this->transformValidationException($exception);
    }

    /**
     * Convert a Laravel auth exception to an API exception.
     *
     * @param  \Exception $exception
     * @return void
     * @throws UnauthenticatedException
     * @throws UnauthorizedException
     */
    protected function transformAuthException(Exception $exception)
    {
        if ($exception instanceof AuthenticationException) {
            throw new UnauthenticatedException;
        }

        if ($exception instanceof AuthorizationException) {
            throw new UnauthorizedException;
        }
    }

    /**
     * Convert an Eloquent exception to an API exception.
     *
     * @param  Exception $exception
     * @return void
     * @throws ResourceNotFoundException
     * @throws RelationNotFoundException
     */
    protected function transformEloquentException(Exception $exception)
    {
        if ($exception instanceof ModelNotFoundException) {
            throw new ResourceNotFoundException;
        }

        if ($exception instanceof BaseRelationNotFoundException) {
            throw new RelationNotFoundException;
        }
    }

    /**
     * Convert a Laravel validation exception to an API exception.
     *
     * @param  Exception $exception
     * @return void
     * @throws ValidationFailedException
     */
    protected function transformValidationException(Exception $exception)
    {
        if ($exception instanceof ValidationException) {
            throw new ValidationFailedException($exception->validator);
        }
    }

    /**
     * Convert API exceptions to error responses.
     *
     * @param  \Flugg\Responder\Exceptions\Http\ApiException $exception
     * @return \Illuminate\Http\JsonResponse
     */
    protected function renderApiError(ApiException $exception): JsonResponse
    {
        return $this->container->make(ErrorResponseBuilder::class)
            ->error($exception->errorCode(), $exception->message())
            ->data($exception->data())
            ->respond($exception->statusCode());
    }
}