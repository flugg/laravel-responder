<?php

namespace Flugg\Responder\Http\Builders;

use Flugg\Responder\Contracts\Pagination\CursorPaginator;
use Flugg\Responder\Contracts\Pagination\Paginator;
use Flugg\Responder\Exceptions\InvalidDataException;
use Flugg\Responder\Http\Resources\Item;
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
     * @throws \Flugg\Responder\Exceptions\InvalidDataException
     * @return $this
     */
    public function make($data = [])
    {
        if (is_array($data)) {
            $this->response = (new SuccessResponse)->setResource(new Item($data));
        } elseif (is_object($data)) {
            $this->response = $this->normalizeData($data);
        } else {
            throw new InvalidDataException;
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
    public function get(): SuccessResponse
    {
        return $this->response;
    }

    /**
     * Normalize the data into a success response value object.
     *
     * @param object $data
     * @throws \Flugg\Responder\Exceptions\InvalidDataException
     * @return \Flugg\Responder\Http\SuccessResponse
     */
    protected function normalizeData(object $data): SuccessResponse
    {
        foreach ($this->config->get('responder.normalizers') as $class => $normalizer) {
            if ($data instanceof $class) {
                return $this->container->makeWith($normalizer, ['data' => $data])->normalize();
            }
        }

        throw new InvalidDataException;
    }

    /**
     * Format the response data.
     *
     * @return array
     */
    protected function format(): array
    {
        if (! $this->formatter) {
            return $this->response->resource()->toArray();
        }

        return $this->formatter->success($this->response);
    }
}
