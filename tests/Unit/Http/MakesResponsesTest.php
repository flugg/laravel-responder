<?php

namespace Flugg\Responder\Tests\Unit;

use Flugg\Responder\Http\MakesResponses;
use Flugg\Responder\Responder;
use Flugg\Responder\Tests\TestCase;
use Mockery;

/**
 * Unit tests for the [Flugg\Responder\Http\MakesApiResponses] trait.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
final class MakesResponsesTest extends TestCase
{
    /**
     * A mock of a [Responder] service class.
     *
     * @var \Mockery\MockInterface
     */
    protected $responder;

    /**
     * The [MakesResponses] trait being tested.
     *
     * @var \Flugg\Responder\Http\MakesResponses
     */
    protected $trait;

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
        $this->trait = $this->getMockForTrait(MakesResponses::class);
    }

    /**
     * Assert that the parameters sent to the [success] method is forwarded to the
     * responder service.
     */
    public function testSuccessMethodShouldCallOnResponder(): void
    {
        $this->responder->shouldReceive('success')->andReturn($responseBuilder = $this->mockSuccessResponseBuilder());

        $result = $this->trait->success($data = ['foo' => 1], $transformer = $this->mockTransformer(), $key = 'foo');

        $this->assertSame($responseBuilder, $result);
        $this->responder->shouldHaveReceived('success')->with($data, $transformer, $key)->once();
    }

    /**
     * Assert that the parameters sent to the [error] method is forwarded to the
     * responder service.
     */
    public function testErrorMethodShouldCallOnResponder(): void
    {
        $this->responder->shouldReceive('error')->andReturn($responseBuilder = $this->mockErrorResponseBuilder());

        $result = $this->trait->error($error = 'error_occured', $message = 'An error has occured.', $data = ['foo' => 1]);

        $this->assertSame($responseBuilder, $result);
        $this->responder->shouldHaveReceived('error')->with($error, $message, $data)->once();
    }
}