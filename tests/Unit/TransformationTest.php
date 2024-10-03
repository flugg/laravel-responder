<?php

namespace Flugg\Responder\Tests\Unit;

use Flugg\Responder\Serializers\NoopSerializer;
use Flugg\Responder\Tests\TestCase;
use Flugg\Responder\Transformation;
use Flugg\Responder\Transformer;

/**
 * Unit tests for the [Flugg\Responder\Transformation] class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
final class TransformationTest extends TestCase
{
    /**
     * A mock of a [TransformBuilder] class.
     *
     * @var \Mockery\MockInterface
     */
    protected $transformBuilder;

    /**
     * The [Transformation] class being tested.
     *
     * @var \Flugg\Responder\Transformer
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

        $this->transformBuilder = $this->mockTransformBuilder();
        $this->transformation = new Transformation($this->transformBuilder);
    }

    /**
     * Assert that the parameters sent to the [transform] method is forwarded to the
     * transform builder.
     */
    public function testTransformMethodShouldCallOnTransformBuilder(): void
    {
        $transformer = $transformer = $this->mockTransformer();
        $transformation = $this->transformation->make($data = ['foo' => 1], $transformer, $resourceKey = 'foo');

        $this->assertSame($this->transformBuilder, $transformation);
        $this->transformBuilder->shouldHaveReceived('resource')->with($data, $transformer, $resourceKey)->once();
        $this->transformBuilder->shouldHaveReceived('serializer')->with(NoopSerializer::class)->once();
    }
}