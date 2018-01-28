<?php

namespace Flugg\Responder\Tests\Unit;

use Flugg\Responder\Contracts\Responder;
use Flugg\Responder\Contracts\SimpleTransformer;
use Flugg\Responder\Tests\TestCase;
use Flugg\Responder\TransformBuilder;
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
     * A mock of a [SimpleTransformer] service class.
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

        $this->transformer = Mockery::mock(SimpleTransformer::class);
        $this->app->instance(SimpleTransformer::class, $this->transformer);
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
    public function testTransformationFunctionShouldTransformUsingSimpleTransformerService()
    {
        $this->transformer->shouldReceive('make')->andReturn($transformBuilder = $this->mockTransformBuilder());

        $result = transformation($data = ['foo' => 1], $transformer = $this->mockTransformer());

        $this->assertSame($transformBuilder, $result);
        $this->transformer->shouldHaveReceived('make')->with($data, $transformer);
    }
}