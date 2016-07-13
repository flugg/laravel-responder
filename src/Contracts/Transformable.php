<?php

namespace Mangopixel\Responder\Contracts;

/**
 * A contract you can apply to your models to map a specific transformer to a model.
 *
 * @package Laravel Responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
interface Transformable
{
    /**
     * The path to the transformer class.
     *
     * @return string
     */
    public static function transformer():string;

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable();

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getRelations();

    /**
     * Determine if the given relation is loaded.
     *
     * @param  string $key
     * @return bool
     */
    public function relationLoaded( $key );
}