<?php

namespace Flugg\Responder\Serializers;

use League\Fractal\Pagination\CursorInterface;
use League\Fractal\Pagination\PaginatorInterface;

/**
 * A no-op serializer class responsible for returning the given data back untouched.
 * Only the raw transformed data is shown, this means meta data wont be visible.
 * The package uses this serializer for the [Transformation] class and it's
 * practically an internal serializer, but feel free to use it elsewhere.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class NoopSerializer extends SuccessSerializer
{
    /**
     * Serialize collection resources.
     *
     * @param  string $resourceKey
     * @param  array  $data
     * @return array
     */
    public function collection($resourceKey, array $data): array
    {
        return $data;
    }

    /**
     * Serialize item resources.
     *
     * @param  string $resourceKey
     * @param  array  $data
     * @return array
     */
    public function item($resourceKey, array $data): array
    {
        return $data;
    }

    /**
     * Serialize null resources.
     *
     * @return null|array
     */
    public function null(): ?array
    {
        return null;
    }

    /**
     * Format meta data.
     *
     * @param  array $meta
     * @return array
     */
    public function meta(array $meta): array
    {
        return [];
    }

    /**
     * Format pagination data.
     *
     * @param  \League\Fractal\Pagination\PaginatorInterface $paginator
     * @return array
     */
    public function paginator(PaginatorInterface $paginator): array
    {
        return [];
    }

    /**
     * Format cursor data.
     *
     * @param  \League\Fractal\Pagination\CursorInterface $cursor
     * @return array
     */
    public function cursor(CursorInterface $cursor): array
    {
        return [];
    }

    /**
     * Merge includes into data.
     *
     * @param  array $transformedData
     * @param  array $includedData
     * @return array
     */
    public function mergeIncludes($transformedData, $includedData): array
    {
        return array_merge($transformedData, $includedData);
    }
}
