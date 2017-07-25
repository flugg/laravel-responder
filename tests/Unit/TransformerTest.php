<?php

namespace Flugg\Responder\Tests\Unit;

use Flugg\Responder\Serializers\NullSerializer;
use Flugg\Responder\Tests\TestCase;
use Flugg\Responder\Transformer;
use Flugg\Responder\Transformers\Transformer as BaseTransformer;
use Mockery;

/**
 * Unit tests for the [Flugg\Responder\Transformer] class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class TransformerTest extends TestCase
{
    /**
     * The transform builder mock.
     *
     * @var \Mockery\MockInterface
     */
    protected $transformBuilder;

    /**
     * The transformer service.
     *
     * @var \Flugg\Responder\Transformer
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

        $this->transformBuilder = $this->mockTransformBuilder();
        $this->transformer = new Transformer($this->transformBuilder);
    }

    /**
     * Test that the parameters sent to the [transform] method is forwarded to the transform builder.
     */
    public function testTransformMethodShouldCallOnTransformBuilder()
    {
        $data        = ['foo' => 1];
        $transformer = $this->mockTransformer();
        $relations   = ['foo', 'bar'];
        $this->transformBuilder->shouldReceive('transform')->andReturn($data);

        $transformation = $this->transformer->transform($data, $transformer, $relations);

        $this->assertEquals($data, $transformation);
        $this->transformBuilder->shouldHaveReceived('resource')->with($data, $transformer)->once();
        $this->transformBuilder->shouldHaveReceived('serializer')->with(NullSerializer::class)->once();
        $this->transformBuilder->shouldHaveReceived('with')->with($relations)->once();
    }
}