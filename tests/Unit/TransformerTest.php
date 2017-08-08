<?php

namespace Flugg\Responder\Tests\Unit;

use Flugg\Responder\Serializers\NullSerializer;
use Flugg\Responder\Tests\TestCase;
use Flugg\Responder\Transformer;

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
     * A mock of a [TransformBuilder] class.
     *
     * @var \Mockery\MockInterface
     */
    protected $transformBuilder;

    /**
     * The [Transformer] service class being tested.
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
     * Assert that the parameters sent to the [transform] method is forwarded to the
     * transform builder.
     */
    public function testTransformMethodShouldCallOnTransformBuilder()
    {
        $transformer = $transformer = $this->mockTransformer();
        $this->transformBuilder->shouldReceive('transform')->andReturn($data = ['foo' => 1]);

        $transformation = $this->transformer->transform($data, $transformer, $relations = ['foo', 'bar']);

        $this->assertEquals($data, $transformation);
        $this->transformBuilder->shouldHaveReceived('resource')->with($data, $transformer)->once();
        $this->transformBuilder->shouldHaveReceived('serializer')->with(NullSerializer::class)->once();
        $this->transformBuilder->shouldHaveReceived('with')->with($relations)->once();
    }
}