<?php

namespace Flugg\Responder\Exceptions;

use Exception;
use Flugg\Responder\Exceptions\Http\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

/**
 * An exception handler responsible for handling exceptions.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class Handler extends ExceptionHandler
{
    use ConvertsExceptions;

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception               $exception
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Exception $exception)
    {
        if ($request->wantsJson()) {
            $this->convertDefaultException($exception);

            if ($exception instanceof HttpException) {
                return $this->renderResponse($exception);
            }
        }

        return parent::render($request, $exception);
    }
}