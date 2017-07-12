<?php

namespace Flugg\Responder\Exceptions;

use Exception;
use Flugg\Responder\Exceptions\Http\ApiException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

/**
 * An exception handler responsible for rendering error responses.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class Handler extends ExceptionHandler
{
    use HandlesApiErrors;

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception               $exception
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Exception $exception)
    {
        $this->transformException($exception);

        if ($exception instanceof ApiException) {
            return $this->renderApiError($exception);
        }

        return parent::render($request, $exception);
    }
}