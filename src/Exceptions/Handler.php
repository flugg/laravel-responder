<?php

namespace Flugg\Responder\Exceptions;

use Exception;
use Flugg\Responder\Exceptions\Http\ApiException;
use Flugg\Responder\Traits\HandlesApiErrors;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    use HandlesApiErrors;

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  Exception                $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function render($request, Exception $exception)
    {
        if ($request->expectsJson()) {
            $this->transformException($exception);

            if ($exception instanceof ApiException) {
                return $this->renderApiError($exception);
            }
        }

        return parent::render($request, $exception);
    }
}