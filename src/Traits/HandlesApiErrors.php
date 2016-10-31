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
use Illuminate\Http\Request;
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
     * Transform a Laravel exception into an API exception.
     *
     * @param  Exception $exception
     * @return void
     */
    protected function transformException(Exception $exception)
    {
        $request = Request::capture();

        if ($request->wantsJson()) {
            $this->transformAuthException($exception);
            $this->transformEloquentException($exception);
            $this->transformValidationException($exception);
        }
    }

    /**
     * Transform a Laravel auth exception into an API exception.
     *
     * @param  Exception $exception
     * @return void
     * @throws UnauthenticatedException
     * @throws UnauthorizedException
     */
    protected function transformAuthException(Exception $exception)
    {
        if ($exception instanceof AuthenticationException) {
            throw new UnauthenticatedException();
        }

        if ($exception instanceof AuthorizationException) {
            throw new UnauthorizedException();
        }
    }

    /**
     * Transform an Eloquent exception into an API exception.
     *
     * @param  Exception $exception
     * @return void
     * @throws ResourceNotFoundException
     * @throws RelationNotFoundException
     */
    protected function transformEloquentException(Exception $exception)
    {
        if ($exception instanceof ModelNotFoundException) {
            throw new ResourceNotFoundException();
        }

        if ($exception instanceof RelationNotFoundException) {
            throw new RelationNotFoundException();
        }
    }

    /**
     * Transform a Laravel validation exception into an API exception.
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
     * Renders any API exception into a JSON error response.
     *
     * @param  ApiException $exception
     * @return JsonResponse
     */
    protected function renderApiError(ApiException $exception):JsonResponse
    {
        return app('responder.error')
            ->setError($exception->getErrorCode(), $exception->getMessage())
            ->addData($exception->getData() ?: [])
            ->respond($exception->getStatusCode());
    }
}