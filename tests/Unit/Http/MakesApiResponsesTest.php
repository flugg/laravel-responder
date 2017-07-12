<?php

namespace Flugg\Responder\Tests\Unit;

use Flugg\Responder\Http\MakesApiResponses;
use Flugg\Responder\Responder;
use Flugg\Responder\Tests\TestCase;
use Illuminate\Http\JsonResponse;
use Mockery;

/**
 * Unit tests for the [Flugg\Responder\Http\MakesApiResponses] trait.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class MakesApiResponsesTest extends TestCase
{
    /**
     * The mock of the responder service.
     *
     * @var \Mockery\MockInterface
     */
    protected $responder;

    /**
     * The controller trait being tested.
     *
     * @var \Flugg\Responder\Http\MakesApiResponses
     */
    protected $trait;

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
        $this->trait = $this->getMockForTrait(MakesApiResponses::class);
    }

    /**
     * Test that the parameters sent to the [error] method is forwarded to the responder service.
     */
    public function testErrorMethodShouldCallOnTheResponder()
    {
        [$error, $message, $data] = ['error_occured', 'An error has occured.', ['foo' => 1]];
        $this->responder->shouldReceive('error')->andReturn($responseBuilder = $this->mockErrorResponseBuilder());

        $result = $this->trait->error($error, $message, $data);

        $this->assertSame($responseBuilder, $result);
        $this->responder->shouldHaveReceived('error')->with($error, $message, $data)->once();
    }

    /**
     * Test that the parameters sent to the [success] method is forwarded to the responder service.
     */
    public function testSuccessMethodShouldCallOnTheResponder()
    {
        [$data, $transformer, $resourceKey] = [['foo' => 1], $this->mockTransformer(), 'foo'];
        $this->responder->shouldReceive('success')->andReturn($responseBuilder = $this->mockSuccessResponseBuilder());

        $result = $this->trait->success($data, $transformer, $resourceKey);

        $this->assertSame($responseBuilder, $result);
        $this->responder->shouldHaveReceived('success')->with($data, $transformer, $resourceKey)->once();
    }
}