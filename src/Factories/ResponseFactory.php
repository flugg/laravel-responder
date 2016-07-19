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
abstract class ResponseFactory
{
    /**
     * @var bool
     */
    protected $includeStatusCode = true;

    /**
     * Constructor.
     *
     * @param bool $includeStatusCode
     */
    public function __construct( bool $includeStatusCode )
    {
        $this->includeStatusCode = $includeStatusCode;
    }

    /**
     * Generate a JSON response.
     *
     * @param  array $data
     * @param  int   $statusCode
     * @return JsonResponse
     */
    public function make( $data, $statusCode ):JsonResponse
    {
        $data = $this->includeStatusCode( $statusCode, $data );

        return response()->json( $data, $statusCode );
    }

    /**
     * Prepend a status code to the response data if status codes are enabled.
     *
     * @param  int   $statusCode
     * @param  array $data
     * @return array
     */
    protected function includeStatusCode( int $statusCode, array $data ):array
    {
        if ( ! $this->includeStatusCode ) {
            return $data;
        }

        return array_merge( [
            'status' => $statusCode
        ], $data );
    }
}