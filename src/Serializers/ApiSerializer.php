<?php

namespace Flugg\Responder\Serializers;

use League\Fractal\Pagination\PaginatorInterface;
use League\Fractal\Resource\ResourceInterface;
use League\Fractal\Serializer\ArraySerializer;

/**
 * This class is the package's own implementation of Fractal's serializers.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class ApiSerializer extends ArraySerializer
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
        return $this->item($resourceKey, $data);
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
        return array_merge($this->null(), [
            'data' => $data
        ]);
    }

    /**
     * Serialize a null resource.
     *
     * @return array
     */
    public function null()
    {
        return [
            'data' => null
        ];
    }

    /**
     * Serialize the meta.
     *
     * @param  array $meta
     * @return array
     */
    public function meta(array $meta)
    {
        return $meta;
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
        $pagination = parent::paginator($paginator)['pagination'];

        $data = [
            'total' => $pagination['total'],
            'count' => $pagination['count'],
            'perPage' => $pagination['per_page'],
            'currentPage' => $pagination['current_page'],
            'totalPages' => $pagination['total_pages'],
            'links' => $pagination['links'],
        ];

        return ['pagination' => $data];
    }

    /**
     * Indicates if includes should be side-loaded.
     *
     * @return bool
     */
    public function sideloadIncludes()
    {
        return true;
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
        $keys = array_keys($includedData);

        foreach ($keys as $key) {
            $includedData[$key] = $includedData[$key]['data'];
        }

        return array_merge($transformedData, $includedData);
    }

    /**
     * Serialize the included data.
     *
     * @param ResourceInterface $resource
     * @param array             $data
     *
     * @return array
     */
    public function includedData(ResourceInterface $resource, array $data)
    {
        return [];
    }
}
