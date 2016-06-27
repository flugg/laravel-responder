<?php

namespace Mangopixel\Responder\Contracts;

use League\Fractal\Resource\ResourceInterface;
use League\Fractal\Scope;

/**
 * A Manager contract used to abstract and bind Fractal to the service container.
 *
 * @package Laravel Responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
interface Manager
{
    /**
     * Main method to kick this all off. Make a resource then pass it over, and use toArray().
     *
     * @param  ResourceInterface $resource
     * @param  string|null       $scopeIdentifier
     * @param  Scope|null        $parentScopeInstance
     * @return Scope
     */
    public function createData( ResourceInterface $resource, $scopeIdentifier = null, Scope $parentScopeInstance = null );
}