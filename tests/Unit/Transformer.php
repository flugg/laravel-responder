<?php

namespace Flugg\Responder\Tests\Unit;

use Flugg\Responder\Tests\TestCase;

/**
 * Collection of unit tests testing [\Flugg\Responder\Transformer].
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class TransformerTest extends TestCase
{
    /**
     *
     *
     * @test
     */
    public function setRelationsMethodShouldSetRelationsOnTransformer()
    {
        // Arrange...
        $transformer = $this->makeTransformer();

        // Act...
        $transformer->setRelations(['foo', 'bar']);

        // Assert...
        $this->assertEquals(['foo', 'bar'], $transformer->getAvailableIncludes());
    }

    /**
     *
     *
     * @test
     */
    public function setRelationsMethodAllowsASingleValue()
    {
        // Arrange...
        $transformer = $this->makeTransformer();

        // Act...
        $transformer->setRelations('foo');

        // Assert...
        $this->assertEquals(['foo'], $transformer->getAvailableIncludes());
    }

    /**
     *
     *
     * @test
     */
    public function setRelationsMethodShouldMergeRelationsWithExisting()
    {
        // Arrange...
        $transformer = $this->makeTransformer();
        $transformer->setRelations('foo');

        // Act...
        $transformer->setRelations('bar');

        // Assert...
        $this->assertEquals(['foo', 'bar'], $transformer->getAvailableIncludes());
    }

    /**
     *
     *
     * @test
     */
    public function getRelationsMethodShouldReturnAllSetIncludes()
    {
        // Arrange...
        $transformer = $this->makeTransformer();
        $transformer->setAvailableIncludes(['foo']);
        $transformer->setDefaultIncludes(['bar']);

        // Act...
        $relations = $transformer->getRelations();

        // Assert...
        $this->assertEquals(['foo', 'bar'], $relations);
    }
}