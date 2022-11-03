<?php

namespace Flugg\Responder;

use Flugg\Responder\ErrorResponse;
use Flugg\Responder\Http\ErrorResponseBuilder;
use Flugg\Responder\Http\SuccessResponseBuilder;
use Flugg\Responder\SuccessResponse;
use Illuminate\Config\Repository;
use Illuminate\Http\JsonResponse;

/**
 * The responder service. This class is responsible for generating JSON API responses.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class Responder
{
    /**
     * The configuration repository used to rerieve optional exception codes.
     *
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    /**
     * The response builder used to build error responses.
     *
     * @var \Flugg\Responder\Http\ErrorResponseBuilder
     */
    protected $errorResponseBuilder;

    /**
     * The response builder used to build success responses.
     *
     * @var \Flugg\Responder\Http\SuccessResponseBuilder
     */
    protected $successResponseBuilder;

    /**
     * Constructor.
     *
     * @param \Illuminate\Config\Repository                $config
     * @param \Flugg\Responder\Http\ErrorResponseBuilder   $errorResponseBuilder
     * @param \Flugg\Responder\Http\SuccessResponseBuilder $successResponseBuilder
     */
    public function __construct(Repository $config, ErrorResponseBuilder $errorResponseBuilder, SuccessResponseBuilder $successResponseBuilder)
    {
        $this->config = $config;
        $this->errorResponseBuilder = $errorResponseBuilder;
        $this->successResponseBuilder = $successResponseBuilder;
    }

    /**
     * Generate an error JSON response.
     *
     * @param  string|null $errorCode
     * @param  int|null    $statusCode
     * @param  mixed       $message
     * @return \Illuminate\Http\JsonResponse
     */
    public function error(string $errorCode = null, int $statusCode = null, $message = null):JsonResponse
    {
        if ($exception = $this->config->get("responder.exceptions.$errorCode")) {
            if (class_exists($exception)) {
                throw new $exception();
            }
        }

        return $this->errorResponseBuilder->setError($errorCode, $message)->respond($statusCode);
    }

    /**
     * Generate a successful JSON response.
     *
     * @param  mixed|null $data
     * @param  int|null   $statusCode
     * @param  array      $meta
     * @return \Illuminate\Http\JsonResponse
     */
    public function success($data = null, $statusCode = null, array $meta = []):JsonResponse
    {
        if (is_integer($data)) {
            list($data, $statusCode, $meta) = [null, $data, is_array($statusCode) ? $statusCode : []];
        }

        if (is_array($statusCode)) {
            list($statusCode, $meta) = [200, $statusCode];
        }

        return $this->successResponseBuilder->transform($data)->addMeta($meta)->respond($statusCode);
    }

    /**
     * Transform the data and return a success response builder.
     *
     * @param  mixed|null           $data
     * @param  callable|string|null $transformer
     * @return \Flugg\Responder\Http\SuccessResponseBuilder
     */
    public function transform($data = null, $transformer = null):SuccessResponseBuilder
    {
        return $this->successResponseBuilder->transform($data, $transformer);
    }
}