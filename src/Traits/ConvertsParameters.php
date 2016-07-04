<?php

namespace Mangopixel\Responder\Traits;

/**
 * Use this trait in your base form request to convert all camel cased parameters to
 * snake case and boolean strings to PHP booleans when accessing the input from
 * the controller.
 *
 * @package Laravel Responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
trait ConvertsParameters
{
    /**
     * Cast boolean strings in parameters to PHP booleans.
     *
     * @var bool
     */
    protected $castBooleans = true;

    /**
     * Enable automatic conversion to snake case of parameter keys.
     *
     * @var bool
     */
    protected $convertToSnakeCase = true;

    /**
     * Check if an input element is set on the request.
     *
     * @param  string $key
     * @return bool
     */
    public function __isset( $key )
    {
        return parent::__isset( snake_case( $key ) );
    }

    /**
     * Get an input element from the request.
     *
     * @param  string $key
     * @return mixed
     */
    public function __get( $key )
    {
        return parent::__get( snake_case( $key ) );
    }

    /**
     * Get the validator instance for the request.
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function getValidatorInstance()
    {
        $this->getInputSource()->replace( $this->getConvertedParameters() );

        return parent::getValidatorInstance();
    }

    /**
     * Cast and convert parameters.
     *
     * @return array
     */
    protected function getConvertedParameters():array
    {
        $parameters = $this->all();

        if ( $this->castBooleans ) {
            $parameters = $this->castBooleans( $parameters );
        }

        if ( $this->convertToSnakeCase ) {
            $parameters = $this->convertToSnakeCase( $parameters );
        }

        return $parameters;
    }

    /**
     * Cast all string booleans to PHP booleans.
     *
     * @param  mixed $input
     * @return array
     */
    protected function castBooleans( $input ):array
    {
        $casted = [ ];

        foreach ( $input as $key => $value ) {
            if ( $value === 'true' || $value === 'false' ) {
                $casted[ $key ] = filter_var( $value, FILTER_VALIDATE_BOOLEAN );
            } else {
                $casted[ $key ] = $value;
            }
        }

        return $casted;
    }

    /**
     * Convert a string or array to snake case.
     *
     * @param  mixed $input
     * @return array|null
     */
    protected function convertToSnakeCase( $input )
    {
        if ( is_null( $input ) ) {
            return null;
        } elseif ( is_array( $input ) ) {
            return $this->convertArrayToSnakeCase( $input );
        }

        return snake_case( $input );
    }

    /**
     * Convert all keys of an array to snake case.
     *
     * @param  array $input
     * @return array
     */
    protected function convertArrayToSnakeCase( array $input ):array
    {
        $converted = [ ];

        foreach ( $input as $key => $value ) {
            $converted[ snake_case( $key ) ] = $value;
        }

        return $converted;
    }
}