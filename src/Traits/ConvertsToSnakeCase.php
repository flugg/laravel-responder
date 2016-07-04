<?php

namespace Mangopixel\Responder\Traits;

/**
 * Use this trait in your base form request to convert all camel cased parameters to
 * snake case when accessing the input from the controller.
 *
 * @package Laravel Responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
trait ConvertToSnakeCase
{
    /**
     * Determine if the request contains a given input item key.
     *
     * @param  string|array $key
     * @return bool
     */
    public function exists( $key )
    {
        return parent::exists( snake_case( $key ) );
    }

    /**
     * Determine if the request contains a non-empty value for an input item.
     *
     * @param  string|array $key
     * @return bool
     */
    public function has( $key )
    {
        return parent::has( snake_case( $key ) );
    }

    /**
     * Get all of the input and files for the request.
     *
     * @return array
     */
    public function all()
    {
        return $this->convertToSnakeCase( parent::all() );
    }

    /**
     * Retrieve an input item from the request.
     *
     * @param  string            $key
     * @param  string|array|null $default
     * @return string|array
     */
    public function input( $key = null, $default = null )
    {
        $key = is_string( $key ) ? snake_case( $key ) : $key;
        $input = $this->getInputSource()->all() + $this->query->all();

        return data_get( $this->convertToSnakeCase( $input ), $key, $default );
    }

    /**
     * Get a subset of the items from the input data.
     *
     * @param  array|mixed $keys
     * @return array
     */
    public function only( $keys )
    {
        return parent::only( $this->convertToSnakeCase( $keys ) );
    }

    /**
     * Get all of the input except for a specified array of items.
     *
     * @param  array|mixed $keys
     * @return array
     */
    public function except( $keys )
    {
        return parent::except( $this->convertToSnakeCase( $keys ) );
    }

    /**
     * Retrieve a query string item from the request.
     *
     * @param  string            $key
     * @param  string|array|null $default
     * @return string|array
     */
    public function query( $key = null, $default = null )
    {
        return parent::query( snake_case( $key ) );
    }

    /**
     * Get an array of all of the files on the request.
     *
     * @return array
     */
    public function allFiles()
    {
        return $this->convertToSnakeCase( parent::allFiles() );
    }

    /**
     * Retrieve a file from the request.
     *
     * @param  string $key
     * @param  mixed  $default
     * @return \Symfony\Component\HttpFoundation\File\UploadedFile|array|null
     */
    public function file( $key = null, $default = null )
    {
        return parent::file( snake_case( $key ) );
    }

    /**
     * Determine if the uploaded data contains a file.
     *
     * @param  string $key
     * @return bool
     */
    public function hasFile( $key )
    {
        return parent::hasFile( snake_case( $key ) );
    }

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
        $validator = parent::getValidatorInstance();

        return $validator->setRules( $this->convertToSnakeCase( $validator->getRules() ) );
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