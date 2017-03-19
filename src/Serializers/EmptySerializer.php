<?php

namespace Flugg\Responder\Serializers;

use League\Fractal\Pagination\CursorInterface;
use League\Fractal\Pagination\PaginatorInterface;

/**
 * This class is an empty serializer which just outputs the data directly.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class EmptySerializer extends ApiSerializer
{
    /**
     * Serialize a collection.
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
     * Serialize an item.
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
     * Serialize a null resource.
     *
     * @return array
     */
    public function null()
    {
        return [];
    }

    /**
     * Serialize the meta.
     *
     * @param  array $meta
     * @return array
     */
    public function meta(array $meta)
    {
        return [];
    }

    /**
     * Serialize the paginator.
     *
     * @param PaginatorInterface $paginator
     *
     * @return array
     */
    public function paginator(PaginatorInterface $paginator)
    {
        return [];
    }

    /**
     * Serialize the cursor.
     *
     * @param CursorInterface $cursor
     *
     * @return array
     */
    public function cursor(CursorInterface $cursor)
    {
        return [];
    }

    /**
     * Merges any relations into the data. The 'data' field is also removed.
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
