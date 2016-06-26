<?php

namespace Mangopixel\Responder\Contracts;

use League\Fractal\Resource\ResourceInterface;
use League\Fractal\Scope;

/**
 *
 *
 * @package Laravel Responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
interface Manageable
{
    /**
     * Main method to kick this all off. Make a resource then pass it over, and use toArray().
     *
     * @param  ResourceInterface $resource
     * @param  string            $scopeIdentifier
     * @param  Scope             $parentScopeInstance
     * @return Scope
     */
    public function createData( ResourceInterface $resource, $scopeIdentifier = null, Scope $parentScopeInstance = null );
}