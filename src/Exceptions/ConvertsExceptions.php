<?php

namespace Flugg\Responder\Exceptions;

use Exception;
use Flugg\Responder\Contracts\Responder;
use Flugg\Responder\Exceptions\Http\HttpException;
use Flugg\Responder\Exceptions\Http\PageNotFoundException;
use Flugg\Responder\Exceptions\Http\RelationNotFoundException;
use Flugg\Responder\Exceptions\Http\UnauthenticatedException;
use Flugg\Responder\Exceptions\Http\UnauthorizedException;
use Flugg\Responder\Exceptions\Http\ValidationFailedException;
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
     * @param  \Exception|\Throwable $exception
     * @param  array      $convert
     * @return void
     */
    protected function convert($exception, array $convert)
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
     * Convert a default exception to an API exception.
     *
     * @param  \Exception|\Throwable $exception
     * @return void
     */
    protected function convertDefaultException($exception)
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
     * @param  \Flugg\Responder\Exceptions\Http\HttpException $exception
     * @return \Illuminate\Http\JsonResponse
     */
    protected function renderResponse(HttpException $exception): JsonResponse
    {
        return app(Responder::class)
            ->error($exception->errorCode(), $exception->message())
            ->data($exception->data())
            ->respond($exception->statusCode(), $exception->getHeaders());
    }
}
