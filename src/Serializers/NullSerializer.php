<?php

namespace Flugg\Responder\Serializers;

use League\Fractal\Pagination\CursorInterface;
use League\Fractal\Pagination\PaginatorInterface;

/**
 * A serializer class responsible for spitting success data back with no changes.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class NullSerializer extends SuccessSerializer
{
    /**
     * Serialize collection resources.
     *
     * @param  string $resourceKey
     * @param  array  $data
     * @return array
     */
    public function collection($resourceKey, array $data)
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
    public function item($resourceKey, array $data)
    {
        return $data;
    }

    /**
     * Serialize null resources.
     *
     * @return array
     */
    public function null()
    {
        return [];
    }

    /**
     * Format meta data.
     *
     * @param  array $meta
     * @return array
     */
    public function meta(array $meta)
    {
        return [];
    }

    /**
     * Format pagination data.
     *
     * @param  \League\Fractal\Pagination\PaginatorInterface $paginator
     * @return array
     */
    public function paginator(PaginatorInterface $paginator)
    {
        return [];
    }

    /**
     * Format cursor data.
     *
     * @param  \League\Fractal\Pagination\CursorInterface $cursor
     * @return array
     */
    public function cursor(CursorInterface $cursor)
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
    public function mergeIncludes($transformedData, $includedData)
    {
        return array_merge($transformedData, $includedData);
    }
}
