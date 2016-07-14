<?php

namespace Flugg\Responder\Contracts;

use Flugg\Responder\Transformer;
use Illuminate\Http\JsonResponse;
use League\Fractal\Resource\ResourceInterface;

/**
 * A responder contract for the responder service which handles the generation of
 * API responses. You may inject this class into your controllers to generate
 * responses directly through the responder service.
 *
 * @package Laravel Responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
interface Responder
{
    /**
     * Generate a successful JSON response.
     *
     * @param  mixed $data
     * @param  int   $statusCode
     * @return JsonResponse
     */
    public function success( $data = null, int $statusCode = 200 ):JsonResponse;

    /**
     * Generate an unsuccessful JSON response.
     *
     * @param  string $error
     * @param  int    $statusCode
     * @param  mixed  $message
     * @return JsonResponse
     */
    public function error( string $error, int $statusCode = 404, $message = null ):JsonResponse;

    /**
     * Transforms the data.
     *
     * @param  mixed            $data
     * @param  Transformer|null $transformer
     * @return ResourceInterface
     */
    public function transform( $data = null, Transformer $transformer = null ):ResourceInterface;

    /**
     * Serializes the data.
     *
     * @param  ResourceInterface $resource
     * @return array
     */
    public function serialize( ResourceInterface $resource ):array;
}