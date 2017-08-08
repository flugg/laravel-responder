<?php

namespace Flugg\Responder\Tests\Unit;

use Flugg\Responder\Contracts\Responder;
use Flugg\Responder\Contracts\Transformer;
use Flugg\Responder\Tests\TestCase;
use Mockery;

/**
 * Unit tests for the helper functions.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class HelpersTest extends TestCase
{
    /**
     * A mock of a [Responder] service class.
     *
     * @var \Mockery\MockInterface
     */
    protected $responder;

    /**
     * A mock of a [Transformer] service class.
     *
     * @var \Mockery\MockInterface
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

        $this->responder = Mockery::mock(Responder::class);
        $this->app->instance(Responder::class, $this->responder);

        $this->transformer = Mockery::mock(Transformer::class);
        $this->app->instance(Transformer::class, $this->transformer);
    }

    /**
     * Assert that the [responder] function should resolve the responder service from the
     * service container.
     */
    public function testResponderFunctionShouldResolveResponderService()
    {
        $result = responder();

        $this->assertSame($this->responder, $result);
    }

    /**
     * Assert that the [transform] function should use the transformer service to transform
     * the data.
     */
    public function testTransformFunctionShouldTransformData()
    {
        $this->transformer->shouldReceive('transform')->andReturn($transformedData = ['bar' => 2]);

        $result = transform($data = ['foo' => 1], $transformer = $this->mockTransformer(), $with = ['foo'], $without = ['bar']);

        $this->assertEquals($transformedData, $result);
        $this->transformer->shouldHaveReceived('transform')->with($data, $transformer, $with, $without);
    }
}