<?php

namespace Flugg\Responder\Http\Builders;

use Flugg\Responder\Contracts\Pagination\CursorPaginator;
use Flugg\Responder\Contracts\Pagination\Paginator;
use Flugg\Responder\Exceptions\InvalidDataException;
use Flugg\Responder\Http\Resources\Item;
use Flugg\Responder\Http\Resources\Primitive;
use Flugg\Responder\Http\SuccessResponse;

/**
 * Builder class for building success responses.
 */
class SuccessResponseBuilder extends ResponseBuilder
{
    /**
     * Response value object.
     *
     * @var \Flugg\Responder\Http\SuccessResponse
     */
    protected $response;

    /**
     * Build a success response.
     *
     * @param mixed $data
     * @param string|null $resourceKey
     * @throws \Flugg\Responder\Exceptions\InvalidDataException
     * @return $this
     */
    public function make($data = null, ?string $resourceKey = null)
    {
        if (is_object($data)) {
            $this->response = $this->normalizeData($data, $resourceKey);
        } elseif (is_array($data)) {
            $this->response = (new SuccessResponse)->setResource(new Item($data, $resourceKey));
        } elseif (is_scalar($data)) {
            $this->response = (new SuccessResponse)->setResource(new Primitive($data, $resourceKey));
        } else {
            $this->response = new SuccessResponse;
        }

        return $this;
    }

    /**
     * Attach a paginator to the success response.
     *
     * @param \Flugg\Responder\Contracts\Pagination\Paginator $paginator
     * @return $this
     */
    public function paginator(Paginator $paginator)
    {
        $this->response->setPaginator($paginator);

        return $this;
    }

    /**
     * Attach a cursor paginator to the success response.
     *
     * @param \Flugg\Responder\Contracts\Pagination\CursorPaginator $paginator
     * @return $this
     */
    public function cursor(CursorPaginator $paginator)
    {
        $this->response->setCursor($paginator);

        return $this;
    }

    /**
     * Retrieve the response data transer object.
     *
     * @return \Flugg\Responder\Http\SuccessResponse
     */
    public function get()
    {
        return $this->response;
    }

    /**
     * Normalize the data into a success response value object.
     *
     * @param object $data
     * @param string|null $resourceKey
     * @throws \Flugg\Responder\Exceptions\InvalidDataException
     * @return \Flugg\Responder\Http\SuccessResponse
     */
    protected function normalizeData(object $data, ?string $resourceKey): SuccessResponse
    {
        foreach ($this->config->get('responder.normalizers') as $class => $normalizer) {
            if ($data instanceof $class) {
                $response = $this->container->makeWith($normalizer, ['data' => $data])->normalize();

                return tap($response, function (SuccessResponse $response) use ($resourceKey) {
                    if ($resourceKey) {
                        $response->resource()->setKey($resourceKey);
                    }
                });
            }
        }

        throw new InvalidDataException;
    }

    /**
     * Format the response data.
     *
     * @return array
     */
    protected function data(): array
    {
        return $this->formatter->success($this->response);
    }
}
