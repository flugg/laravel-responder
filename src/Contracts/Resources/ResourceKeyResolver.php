<?php

namespace Flugg\Responder\Contracts\Resources;

/**
 * A contract for resolving resource keys.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
interface ResourceKeyResolver
{
    /**
     * Register a transformable to resource key binding.
     *
     * @param  string|array $transformable
     * @param  string       $resourceKey
     * @return void
     */
    public function bind($transformable, string $resourceKey);

    /**
     * Resolve a resource key from the given data.
     *
     * @param  mixed $data
     * @return string
     */
    public function resolve($data);
}