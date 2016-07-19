<?php

namespace Flugg\Responder\Factories;

use Illuminate\Http\JsonResponse;

/**
 *
 *
 * @package Laravel Responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class ErrorResponseFactory extends ResponseFactory
{
    /**
     * Generate a successful JSON response.
     *
     * @param  mixed $errorCode
     * @param  int   $statusCode
     * @param  array $message
     * @return JsonResponse
     */
    public function make( $errorCode, $statusCode = 500, $message = [ ] ):JsonResponse
    {
        $response = $this->getErrorResponse( $errorCode, $statusCode );
        $messages = $this->getErrorMessages( $errorCode, $message );

        if ( count( $messages ) === 1 ) {
            $response[ 'error' ][ 'message' ] = $messages[ 0 ];
        } else if ( count( $messages ) > 1 ) {
            $response[ 'error' ][ 'messages' ] = $messages;
        }

        return response()->json( $response, $statusCode );
    }

    /**
     * Get the skeleton for an error response.
     *
     * @param string $errorCode
     * @param int    $statusCode
     * @return array
     */
    protected function getErrorResponse( string $errorCode, int $statusCode ):array
    {
        $response = [
            'success' => false,
            'error' => [
                'code' => $errorCode
            ]
        ];

        return $this->includeStatusCode( $statusCode, $response );
    }

    /**
     * Get any error messages for the response. If no message can be found it will
     * try to resolve a set message from the translator.
     *
     * @param  string $errorCode
     * @param  mixed  $message
     * @return array
     */
    protected function getErrorMessages( string $errorCode, $message ):array
    {
        if ( is_array( $message ) ) {
            return $message;

        } elseif ( is_string( $message ) ) {
            if ( strlen( $message ) === 0 ) {
                return [ ];
            }

            return [ $message ];
        }

        return [ app( 'translator' )->trans( 'errors.' . $errorCode ) ];
    }
}