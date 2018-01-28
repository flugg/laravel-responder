<?php

namespace Flugg\Responder\Tests\Unit;

use Flugg\Responder\Serializers\NoopSerializer;
use Flugg\Responder\SimpleTransformer;
use Flugg\Responder\Tests\TestCase;
use Flugg\Responder\Transformer;

/**
 * Unit tests for the [Flugg\Responder\SimpleTransformer] class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class SimpleTransformerTest extends TestCase
{
    /**
     * A mock of a [TransformBuilder] class.
     *
     * @var \Mockery\MockInterface
     */
    protected $transformBuilder;

    /**
     * The [SimpleTransformer] service class being tested.
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
        $this->transformer = new SimpleTransformer($this->transformBuilder);
    }

    /**
     * Assert that the parameters sent to the [transform] method is forwarded to the
     * transform builder.
     */
    public function testTransformMethodShouldCallOnTransformBuilder()
    {
        $transformation = $this->transformer->make($data = ['foo' => 1], $transformer = $this->mockTransformer());

        $this->assertSame($this->transformBuilder, $transformation);
        $this->transformBuilder->shouldHaveReceived('resource')->with($data, $transformer)->once();
        $this->transformBuilder->shouldHaveReceived('serializer')->with(NoopSerializer::class)->once();
    }
}