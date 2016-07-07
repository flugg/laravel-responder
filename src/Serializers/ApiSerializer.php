<?php

namespace Mangopixel\Responder\Serializers;

use League\Fractal\Serializer\ArraySerializer;

class ApiSerializer extends ArraySerializer
{
    /**
     * Serialize a collection.
     *
     * @param string $resourceKey
     * @param array  $data
     *
     * @return array
     */
    public function collection( $resourceKey, array $data )
    {
        return $this->item( $data );
    }

    /**
     * Serialize an item.
     *
     * @param string $resourceKey
     * @param array  $data
     *
     * @return array
     */
    public function item( $resourceKey, array $data )
    {
        return array_merge( $this->null(), [
            'data' => $data
        ] );
    }

    /**
     * Serialize a null resource.
     *
     * @return array
     */
    public function null()
    {
        return [
            'success' => true,
            'data' => null
        ];
    }
}