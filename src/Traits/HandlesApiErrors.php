<?php

namespace Mangopixel\Responder\Traits;

use Illuminate\Foundation\Exceptions\Handler;
use Mangopixel\Responder\Exceptions\UnauthorizedException;
use Mangopixel\Responder\Exceptions\ValidationFailedException;


/**
 * You may apply this trait to your exceptions handler to give you access to methods
 * you may use to let the package catch and handle any API exceptions.
 *
 * @package Laravel Responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
trait CatchesApiErrors
{
    /**
     * Transforms and renders api responses.
     *
     * @param  \Exception $e
     * @return $this
     */
    protected function handleApiErrors( Exception $e ):Handler
    {
        $this->transformExceptions( $e );

        if ( $e instanceof ApiException ) {
            return $this->renderApiResponse( $e );
        } else {
            $this->renderConsoleResponse( $e );
        }

        return $this;
    }

    /**
     * Renders readable responses for console, useful for testing.
     *
     * @param  \Exception $e
     * @return $this
     */
    protected function handleTestErrors( Exception $e ):Handler
    {
        if ( ! $e instanceof ApiException && app()->runningInConsole() ) {
            $this->renderConsoleResponse( $e );
        }

        return $this;
    }

    /**
     * Transform Laravel exceptions into API exceptions.
     *
     * @param  \Exception $e
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

        return ApiResponse::error( $e->getErrorCode(), $e->getStatusCode(), $message );
    }

    /**
     * Render an exception into an HTTP response for the console, used for debugging in test mode.
     *
     * @param  Exception $e
     * @return void
     */
    protected function renderConsoleResponse( Exception $e )
    {
        $this->renderForConsole( new ConsoleOutput( ConsoleOutput::VERBOSITY_VERBOSE ), $e );
    }

    /**
     * Render an exception to the console.
     */
    abstract public function renderForConsole( $output, Exception $e );
}