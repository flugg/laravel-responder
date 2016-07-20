<?php

namespace Flugg\Responder\Traits;

use Exception;
use Flugg\Responder\Exceptions\ApiException;
use Flugg\Responder\Exceptions\ResourceNotFoundException;
use Flugg\Responder\Exceptions\UnauthorizedException;
use Flugg\Responder\Exceptions\ValidationFailedException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

/**
 * Use this trait in your exceptions handler to give you access to methods you may
 * use to let the package catch and handle any API exceptions.
 *
 * @package Laravel Responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
trait HandlesApiErrors
{
    /**
     * Transforms and renders api responses.
     *
     * @param  Exception $e
     * @return JsonResponse
     */
    protected function renderApiErrors( Exception $e ):JsonResponse
    {
        $this->transformExceptions( $e );

        if ( $e instanceof ApiException ) {
            return $this->renderApiResponse( $e );
        }
    }

    /**
     * Transform Laravel exceptions into API exceptions.
     *
     * @param  Exception $e
     * @return void
     * @throws ResourceNotFoundException
     * @throws UnauthorizedException
     */
    protected function transformExceptions( Exception $e )
    {
        if ( $e instanceof AuthorizationException ) {
            throw new UnauthorizedException();
        }

        if ( $e instanceof ModelNotFoundException ) {
            throw new ResourceNotFoundException();
        }
    }

    /**
     * Render an exception into an API response.
     *
     * @param  ApiException $e
     * @return JsonResponse
     */
    protected function renderApiResponse( ApiException $e ):JsonResponse
    {
        $message = $e instanceof ValidationFailedException ? $e->getValidationMessages() : $e->getMessage();

        return app( 'responder' )->error( $e->getErrorCode(), $e->getStatusCode(), $message );
    }
}