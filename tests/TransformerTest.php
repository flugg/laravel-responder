<?php

namespace Flugg\Responder\Tests;

use Flugg\Responder\Serializers\ApiSerializer;
use Flugg\Responder\Transformer;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Scope;
use Mockery;

/**
 * Collection of unit tests for the [\Flugg\Responder\Transformer] class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class TransformerTest extends TestCase
{
    /**
     * Test that the [getRelations] method returns available relations.
     *
     * @test
     * @covers \Flugg\Responder\Transformer::getRelations
     */
    public function getRelationsMethodShouldReturnAvailableRelations()
    {
        // Arrange...
        $transformer = new class extends Transformer
        {
            protected $relations = ['foo', 'bar'];
        };

        // Act...
        $relations = $transformer->getRelations();

        // Assert...
        $this->assertEquals(['foo', 'bar'], $relations);
    }

    /**
     * Test that the [getRelations] method does not return wildcard symbol.
     *
     * @test
     * @covers \Flugg\Responder\Transformer::getRelations
     */
    public function getRelationsMethodShouldNotReturnWildcard()
    {
        // Arrange...
        $transformer = new class extends Transformer
        {
            protected $relations = ['*'];
        };

        // Act...
        $relations = $transformer->getRelations();

        // Assert...
        $this->assertEquals([], $relations);
    }

    /**
     * Test that the [allRelationsAllowed] method returns true if the $relations array
     * doesn't contain a wilcard.
     *
     * @test
     * @covers \Flugg\Responder\Transformer::allRelationsAllowed
     */
    public function allRelationsAllowedMethodShouldReturnFalseIfRelationsArrayHasNoWildcard()
    {
        // Arrange...
        $transformer = new class extends Transformer
        {
            protected $relations = ['foo', 'bar'];
        };

        // Act...
        $allowAllRelations = $transformer->allRelationsAllowed();

        // Assert...
        $this->assertFalse($allowAllRelations);
    }

    /**
     * Test that the [allRelationsAllowed] method returns true if the $relations array
     * contains wilcard.
     *
     * @test
     * @covers \Flugg\Responder\Transformer::allRelationsAllowed
     */
    public function allRelationsAllowedMethodShouldReturnTrueIfRelationsArrayHasWildcard()
    {
        // Arrange...
        $transformer = new class extends Transformer
        {
            protected $relations = ['*'];
        };

        // Act...
        $allowAllRelations = $transformer->allRelationsAllowed();

        // Assert...
        $this->assertTrue($allowAllRelations);
    }

    /**
     * Test that the [allRelationsAllowed] method returns true if the $relations array
     * contains wilcard.
     *
     * @test
     * @covers \Flugg\Responder\Transformer::callIncludeMethod
     */
    public function processIncludedResources()
    {
        // Arrange...
        $model = $this->makeModel();
        $transformer = new class extends Transformer
        {
            protected $availableIncludes = ['foo'];
        };

        $scope = new Scope((new Manager)->setSerializer(new ApiSerializer)->parseIncludes('foo'), new Item($model, $transformer));

        // Act...
        $data = $transformer->processIncludedResources($scope, $model);
        dd($data);

        // Assert...
        $this->assertEquals(['foo' => null], $data);
    }
}