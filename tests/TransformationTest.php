<?php

namespace Flugg\Responder\Tests;

use Closure;
use Flugg\Responder\Exceptions\InvalidTransformerException;
use Illuminate\Database\Eloquent\Model;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\NullResource;

/**
 * Collection of unit tests for the [\Flugg\Responder\Transformation] class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 *
 * @covers \Flugg\Responder\Transformation::__construct
 */
class TransformationTest extends TestCase
{
    /**
     * Test that the resource instance is set to [\League\Fractal\Resource\NullResource]
     * when given no data to the [transform] method.
     *
     * @test
     * @covers \Flugg\Responder\Transformation::make
     */
    public function makeMethodShouldSetResourceToNullResourceWhenGivenNoData()
    {
        // Arrange...

        // Act...

        // Assert...
    }
}