<?php

namespace Flugg\Responder\Tests\Unit;

use Flugg\Responder\Contracts\Responder;
use Flugg\Responder\Tests\TestCase;
use Flugg\Responder\Transformation;
use Mockery;

/**
 * Unit tests for the helper functions.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
final class HelpersTest extends TestCase
{
    /**
     * A mock of a [Responder] service class.
     *
     * @var \Mockery\MockInterface
     */
    protected $responder;

    /**
     * A mock of a [Transformation] class.
     *
     * @var \Mockery\MockInterface
     */
    protected $transformation;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->responder = Mockery::mock(Responder::class);
        $this->app->instance(Responder::class, $this->responder);

        $this->transformation = Mockery::mock(Transformation::class);
        $this->app->instance(Transformation::class, $this->transformation);
    }

    /**
     * Assert that the [responder] function should resolve the responder service from the
     * service container.
     */
    public function testResponderFunctionShouldResolveResponderService(): void
    {
        $result = responder();

        $this->assertSame($this->responder, $result);
    }

    /**
     * Assert that the [transform] function should use the transformer service to transform
     * the data.
     */
    public function testTransformationFunctionShouldTransformUsingTransformationClass(): void
    {
        $this->transformation->shouldReceive('make')->andReturn($transformBuilder = $this->mockTransformBuilder());

        $result = transformation($data = ['foo' => 1], $transformer = $this->mockTransformer());

        $this->assertSame($transformBuilder, $result);
        $this->transformation->shouldHaveReceived('make')->with($data, $transformer);
    }
}