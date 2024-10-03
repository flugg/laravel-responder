<?php

namespace Flugg\Responder\Tests\Unit\Facades;

use Flugg\Responder\Facades\Transformation as TransformationFacade;
use Flugg\Responder\Tests\TestCase;
use Flugg\Responder\Transformation;
use Mockery;

/**
 * Unit tests for the [Flugg\Responder\Facades\Transformation] facade.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
final class TransformationTest extends TestCase
{
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

        $this->transformation = Mockery::mock(Transformation::class);
        $this->app->instance(Transformation::class, $this->transformation);
    }

    /**
     * Assert that the parameters sent to the [transform] method is forwarded to the
     * transformer service.
     */
    public function testMakeMethodShouldCallOnTransformer(): void
    {
        $this->transformation->shouldReceive('make')->andReturn($transformBuilder = $this->mockTransformBuilder());

        $result = TransformationFacade::make($data = ['foo' => 1]);

        $this->assertSame($transformBuilder, $result);
        $this->transformation->shouldHaveReceived('make')->with($data);
    }
}