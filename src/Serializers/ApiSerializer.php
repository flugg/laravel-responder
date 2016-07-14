<?php

namespace Flugg\Responder\Serializers;

use League\Fractal\Resource\ResourceInterface;
use League\Fractal\Serializer\ArraySerializer;

/**
 * Laravel Responder's own default implementation of Fractal's serializers.
 *
 * @package Laravel Responder
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
    public function collection( $resourceKey, array $data )
    {
        return $this->item( $resourceKey, $data );
    }

    /**
     * Serialize an item.
     *
     * @param  string $resourceKey
     * @param  array  $data
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

    /**
     * Serialize the meta.
     *
     * @param  array $meta
     * @return array
     */
    public function meta( array $meta )
    {
        return $meta;
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
    public function mergeIncludes( $transformedData, $includedData )
    {
        $resourceKey = key( $includedData );

        if ( $resourceKey ) {
            $includedData[ $resourceKey ] = $includedData[ $resourceKey ][ 'data' ];
        }

        return array_merge( $transformedData, $includedData );
    }

    /**
     * Serialize the included data.
     *
     * @param ResourceInterface $resource
     * @param array             $data
     *
     * @return array
     */
    public function includedData( ResourceInterface $resource, array $data )
    {
        return [ ];
    }
}