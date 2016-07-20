<?php

namespace Flugg\Responder;

use Flugg\Responder\Contracts\Responder as ResponderContract;
use Flugg\Responder\Factories\ResponseFactory;
use Illuminate\Http\JsonResponse;

/**
 * The responder service. This class is responsible for generating JSON API responses.
 * It can also transform and serialize data using Fractal behind the scenes.
 *
 * @package Laravel Responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class Responder implements ResponderContract
{
    /**
     *
     *
     * @var ResponseFactory
     */
    protected $successFactory;

    /**
     *
     *
     * @var ResponseFactory
     */
    protected $errorFactory;

    /**
     * Constructor.
     *
     * @param ResponseFactory $successFactory
     * @param ResponseFactory $errorFactory
     */
    public function __construct( ResponseFactory $successFactory, ResponseFactory $errorFactory )
    {
        $this->successFactory = $successFactory;
        $this->errorFactory = $errorFactory;
    }

    /**
     * Generate a successful JSON response.
     *
     * @param  mixed $data
     * @param  int   $statusCode
     * @param  array $meta
     * @return JsonResponse
     */
    public function success( $data = null, $statusCode = 200, array $meta = [ ] ):JsonResponse
    {
        if ( is_integer( $data ) ) {
            $statusCode = is_array( $statusCode ) ? $statusCode : [ ];
            list( $data, $statusCode, $meta ) = [ null, $data, $statusCode ];
        } elseif ( is_array( $statusCode ) ) {
            list( $statusCode, $meta ) = [ 200, $statusCode ];
        }

        return $this->successFactory->make( $data, $statusCode, $meta );
    }

    /**
     * Generate an unsuccessful JSON response.
     *
     * @param  string $errorCode
     * @param  int    $statusCode
     * @param  mixed  $message
     * @return JsonResponse
     */
    public function error( string $errorCode, int $statusCode = 500, $message = null ):JsonResponse
    {
        return $this->errorFactory->make( $errorCode, $statusCode, $message );
    }
}