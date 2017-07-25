<?php

namespace Flugg\Responder\Tests\Unit\Transformers;

use Flugg\Responder\Tests\TestCase;
use Flugg\Responder\Transformers\Transformer;
use Mockery;

/**
 * Unit tests for the abstract [Flugg\Responder\Transformers\Transformer] class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class TransformerTest extends TestCase
{
    /**
     * The transformer being tested.
     *
     * @var \Flugg\Responder\Transformers\Transformer
     */
    protected $transformer;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->transformer = Mockery::mock(Transformer::class);
    }

    /**
     * Test that the [allRelationsAllowed] method returns true if the relations array is set to a wildcard.
     */
    public function testAllRelationsAllowedMethodShouldReturnTrueOnWildcard()
    {
        $transformer = new class extends Transformer {
            protected $relations = ['*'];
        };

        $result = $transformer->allRelationsAllowed();

        $this->assertTrue($result);
    }

    /**
     * Test that the [allRelationsAllowed] method returns false if the relations array is not set to a wildcard.
     */
    public function testAllRelationsAllowedMethodShouldReturnFalseIfNotWildcard()
    {
        $transformer = new class extends Transformer {
            protected $relations = ['foo'];
        };

        $result = $transformer->allRelationsAllowed();

        $this->assertFalse($result);
    }

    /**
     * Test that the [allRelationsAllowed] method returns false if the relations array is not set to a wildcard.
     */
    public function testGetRelations()
    {
        $transformer = new class extends Transformer {
            protected $relations = ['foo', 'bar'];
        };

        $result = $transformer->allRelationsAllowed();

        $this->assertFalse($result);
    }
}