<?php

namespace Flugg\Responder\Testing;

use Flugg\Responder\Responder;
use Illuminate\Http\JsonResponse;

/**
 * A trait to be used by test case classes to give access to additional assertion methods.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
trait MakesApiRequests
{
    /**
     * Assert that the response is a valid success response.
     *
     * @param  mixed $data
     * @param  int   $status
     * @return $this
     */
    protected function seeSuccess($data = null, $status = 200)
    {
        $response = $this->seeSuccessResponse($data, $status);
        $this->seeSuccessData($response->getData(true)['data']);

        return $this;
    }

    /**
     * Assert that the response is a valid success response.
     *
     * @param  mixed $data
     * @param  int   $status
     * @return $this
     */
    protected function seeSuccessEquals($data = null, $status = 200)
    {
        $response = $this->seeSuccessResponse($data, $status);
        $this->seeJsonEquals($response->getData(true));

        return $this;
    }

    /**
     * Assert that the response data contains the given structure.
     *
     * @param  mixed $data
     * @return $this
     */
    protected function seeSuccessStructure($data = null)
    {
        $this->seeJsonStructure([
            'data' => $data,
        ]);

        return $this;
    }

    /**
     * Assert that the response is a valid success response.
     *
     * @param  mixed $data
     * @param  int   $status
     * @return \Illuminate\Http\JsonResponse
     */
    protected function seeSuccessResponse($data = null, $status = 200): JsonResponse
    {
        $response = $this->app->make(Responder::class)->success($data, $status);

        $this->seeStatusCode($response->getStatusCode())->seeJson([
            'success' => true,
            'status' => $response->getStatusCode(),
        ])->seeJsonStructure(['data']);

        return $response;
    }

    /**
     * Assert that the response data contains given values.
     *
     * @param  mixed $data
     * @return $this
     */
    protected function seeSuccessData($data = null)
    {
        collect($data)->each(function ($value, $key) {
            if (is_array($value)) {
                $this->seeSuccessData($value);
            } else {
                $this->seeJson([$key => $value]);
            }
        });

        return $this;
    }

    /**
     * Decodes JSON response and returns the data.
     *
     * @param  string|array|null $attributes
     * @return array
     */
    protected function getSuccessData($attributes = null)
    {
        $rawData = $this->decodeResponseJson()['data'];

        if (is_null($attributes)) {
            return $rawData;
        } elseif (is_string($attributes)) {
            return array_get($rawData, $attributes);
        }

        $data = [];

        foreach ($attributes as $attribute) {
            $data[] = array_get($rawData, $attribute);
        }

        return $data;
    }

    /**
     * Assert that the response is a valid error response.
     *
     * @param  string   $error
     * @param  int|null $status
     * @return $this
     */
    protected function seeError(string $error, int $status = null)
    {
        if (! is_null($status)) {
            $this->seeStatusCode($status);
        }

        if ($this->app->config->get('responder.status_code')) {
            $this->seeJson([
                'status' => $status,
            ]);
        }

        return $this->seeJson([
            'success' => false,
        ])->seeJsonSubset([
            'error' => [
                'code' => $error,
            ],
        ]);
    }

    /**
     * Asserts that the status code of the response matches the given code.
     *
     * @param  int $status
     * @return $this
     */
    abstract protected function seeStatusCode($status);

    /**
     * Assert that the response contains JSON.
     *
     * @param  array|null $data
     * @param  bool       $negate
     * @return $this
     */
    abstract public function seeJson(array $data = null, $negate = false);

    /**
     * Assert that the JSON response has a given structure.
     *
     * @param  array|null $structure
     * @param  array|null $responseData
     * @return $this
     */
    abstract public function seeJsonStructure(array $structure = null, $responseData = null);

    /**
     * Assert that the response is a superset of the given JSON.
     *
     * @param  array $data
     * @return $this
     */
    abstract protected function seeJsonSubset(array $data);

    /**
     * Assert that the response contains an exact JSON array.
     *
     * @param  array $data
     * @return $this
     */
    abstract public function seeJsonEquals(array $data);

    /**
     * Validate and return the decoded response JSON.
     *
     * @return array
     */
    abstract protected function decodeResponseJson();
}
