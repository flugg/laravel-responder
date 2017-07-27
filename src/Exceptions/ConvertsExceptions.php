<?php

namespace Flugg\Responder\Exceptions;

use Exception;
use Flugg\Responder\Exceptions\Http\ApiException;
use Flugg\Responder\Exceptions\Http\PageNotFoundException;
use Flugg\Responder\Exceptions\Http\RelationNotFoundException;
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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * A trait used by exception handlers to transform and render error responses.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
trait ConvertsExceptions
{
    /**
     * A list of default exception types that should not be converted.
     *
     * @var array
     */
    protected $dontConvert = [];

    /**
     * Convert an exception to another exception
     *
     * @param  \Exception $exception
     * @param  array      $convert
     * @return void
     */
    protected function convert(Exception $exception, array $convert)
    {
        foreach ($convert as $source => $target) {
            if ($exception instanceof $source) {
                if (is_callable($target)) {
                    $target($exception);
                }

                throw new $target;
            }
        }
    }

    /**
     * Convert a Laravel exception to an API exception.
     *
     * @param  \Exception $exception
     * @return void
     */
    protected function convertDefaultException(Exception $exception)
    {
        $this->convert($exception, array_diff_key([
            AuthenticationException::class => UnauthenticatedException::class,
            AuthorizationException::class => UnauthorizedException::class,
            NotFoundHttpException::class => PageNotFoundException::class,
            ModelNotFoundException::class => PageNotFoundException::class,
            BaseRelationNotFoundException::class => RelationNotFoundException::class,
            ValidationException::class => function ($exception) {
                throw new ValidationFailedException($exception->validator);
            },
        ], array_flip($this->dontConvert)));
    }

    /**
     * Render an error response from an API exception.
     *
     * @param  \Flugg\Responder\Exceptions\Http\ApiException $exception
     * @return \Illuminate\Http\JsonResponse
     */
    protected function renderResponse(ApiException $exception): JsonResponse
    {
        return $this->container->make(ErrorResponseBuilder::class)
            ->error($exception->errorCode(), $exception->message())
            ->data($exception->data())
            ->respond($exception->statusCode());
    }
}