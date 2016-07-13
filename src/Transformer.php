<?php

namespace Mangopixel\Responder;

use Illuminate\Support\Collection;
use League\Fractal\Scope;
use League\Fractal\TransformerAbstract;
use Mangopixel\Responder\Contracts\Transformable;

/**
 * An abstract base transformer. All transformers should extend this, and this class
 * itself extends the Fractal transformer.
 *
 * @package Laravel Responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
abstract class Transformer extends TransformerAbstract
{
    protected $model;

    public function __construct( Transformable $model = null )
    {
        $this->model = $model;
    }

    public function processIncludedResources( Scope $scope, $data )
    {
        $includedData = [ ];
        $includes = array_merge( $this->getDefaultIncludes(), $this->getAvailableIncludes() );

        foreach ( $includes as $include ) {
            $includedData = $this->includeResourceIfAvailable( $scope, $data, $includedData, $include );
        }

        return $includedData === [ ] ? false : $includedData;
    }

    protected function includeResourceIfAvailable( Scope $scope, $data, $includedData, $include )
    {
        if ( $resource = $this->callIncludeMethod( $scope, $include, $data ) ) {
            $childScope = $scope->embedChildScope( $include, $resource );

            $includedData[ $include ] = $childScope->toArray();
        }

        return $includedData;
    }

    protected function callIncludeMethod( Scope $scope, $includeName, $data )
    {
        if ( ! $data->relationLoaded( $includeName ) ) {
            return false;
        }

        $responder = app( Responder::class );
        $data = $data->$includeName;

        if ( $data instanceof Transformable ) {
            $transformer = $data::transformer();
            $resource = $responder->transform( $data, new $transformer( $data ) );

        } elseif ( $data instanceof Collection && $data->count() > 0 ) {
            $model = get_class( $data->first() );
            $transformer = $model::transformer();
            $resource = $responder->transform( $data, new $transformer( $model ) );

        } else {
            $resource = $responder->transform();
        }

        return $resource;
    }

    /**
     * Getter for availableIncludes.
     *
     * @return array
     */
    public function getAvailableIncludes()
    {
        return array_keys( $this->model->getRelations() );
    }
}