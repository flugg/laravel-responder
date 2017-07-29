<?php

namespace Flugg\Responder\Tests\Unit\Facades;

use Flugg\Responder\Contracts\Transformer as TransformerContract;
use Flugg\Responder\Facades\Transformer;
use Flugg\Responder\Tests\TestCase;
use Mockery;

/**
 * Unit tests for the [Flugg\Responder\Facades\Transformer] facade.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class TransformerTest extends TestCase
{
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

        $this->transformer = Mockery::mock(TransformerContract::class);
        $this->app->instance(TransformerContract::class, $this->transformer);
    }

    /**
     * Assert that the parameters sent to the [transform] method is forwarded to the
     * transformer service.
     */
    public function testTransformMethodShouldCallOnTransformer()
    {
        $this->transformer->shouldReceive('transform')->andReturn($transformedData = ['bar' => 2]);

        $result = Transformer::transform($data = ['foo' => 1]);

        $this->assertEquals($transformedData, $result);
        $this->transformer->shouldHaveReceived('transform')->with($data);
    }
}