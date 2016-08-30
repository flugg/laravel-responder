<?php

namespace Flugg\Responder\Contracts;

/**
 * A contract you can apply to your models to map a specific transformer to a model.
 *
 * @package flugger/laravel-responder
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
    public static function transformer();

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
    public function relationLoaded($key);

    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray();
}