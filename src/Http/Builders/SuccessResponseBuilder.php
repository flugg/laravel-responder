<?php

namespace Flugg\Responder\Http\Builders;

use Flugg\Responder\Contracts\Pagination\CursorPaginator;
use Flugg\Responder\Contracts\Pagination\Paginator;
use Flugg\Responder\Exceptions\InvalidDataException;
use Flugg\Responder\Http\Resource;
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
     * @return $this
     * @throws \Flugg\Responder\Exceptions\InvalidDataException
     */
    public function make($data = [])
    {
        if (is_array($data)) {
            $this->response = (new SuccessResponse())->setResource(new Resource($data));
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
        $this->response->setCursorPaginator($paginator);

        return $this;
    }

    /**
     * Normalize the data into a success response value object.
     *
     * @param object $data
     * @return \Flugg\Responder\Http\SuccessResponse
     * @throws \Flugg\Responder\Exceptions\InvalidDataException
     */
    protected function normalizeData(object $data): SuccessResponse
    {
        foreach ($this->normalizers as $class => $normalizer) {
            if ($data instanceof $class) {
                return $this->container->make($normalizer)->normalize($data);
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
        if (!$this->formatter) {
            return $this->response->resource();
        }

        return $this->formatter->success($this->response);
    }
}
