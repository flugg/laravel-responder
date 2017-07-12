<?php

namespace Flugg\Responder\Tests\Unit;

use Flugg\Responder\Contracts\Responder;
use Flugg\Responder\Contracts\Transformer;
use Flugg\Responder\Tests\TestCase;
use Mockery;

/**
 * Unit tests for the helpers file.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class HelpersTest extends TestCase
{
    /**
     * The mock of the responder service.
     *
     * @var \Mockery\MockInterface
     */
    protected $responder;

    /**
     * The mock of the transformer service.
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
     *
     */
    public function testResponderFunctionShouldResolveResponderFromTheContainer()
    {
        $result = responder();

        $this->assertSame($this->responder, $result);
    }

    /**
     *
     */
    public function testTransformFunctionShouldUseTheTransformerServiceToTransform()
    {
        [$data, $transformer, $with, $without] = [['foo' => 1], $this->mockTransformer(), ['foo'], ['bar']];
        $this->transformer->shouldReceive('transform')->andReturn($transformedData = ['bar' => 2]);

        $result = transform($data, $transformer, $with, $without);

        $this->assertEquals($transformedData, $result);
        $this->transformer->shouldHaveReceived('transform')->with($data, $transformer, $with, $without);
    }
}