<?php

namespace Flugg\Responder\Exceptions;

use Exception;
use Flugg\Responder\Traits\HandlesApiErrors;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    use HandlesApiErrors;

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  Exception                $e
     * @return \Illuminate\Http\Response
     */
    public function render( $request, Exception $e )
    {
        $this->transformExceptions( $e );

        if ( $e instanceof ApiException ) {
            return $this->renderApiError( $e );
        }

        return parent::render( $request, $e );
    }
}
