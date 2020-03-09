<?php

namespace Flugg\Responder\Contracts\Http\Builders;

use Illuminate\Http\JsonResponse;

/**
 * A contract for building responses.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
interface ResponseBuilder
{
    /**
     * Set a response formatter.
     *
     * @param ResponseFormatter|string $formatter
     * @return $this
     */
    public function formatter($formatter);

    /**
     * Decorate the response with the given decorators.
     *
     * @param string|string[] $decorators
     * @return $this
     */
    public function decorate($decorators);

    /**
     * Add additional meta data to the response content.
     *
     * @param array $meta
     * @return $this
     */
    public function meta(array $meta);

    /**
     * Respond with a JSON response.
     *
     * @param int|null $status
     * @param array $headers
     * @return JsonResponse
     */
    public function respond(int $status = null, array $headers = []): JsonResponse;
}
