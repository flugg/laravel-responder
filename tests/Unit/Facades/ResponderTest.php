<?php

namespace Flugg\Responder\Tests\Unit\Facades;

use Flugg\Responder\Contracts\Responder as ResponderContract;
use Flugg\Responder\Facades\Responder;
use Flugg\Responder\Tests\TestCase;
use Mockery;

/**
 * Unit tests for the [Flugg\Responder\Facades\Responder] facade.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
final class ResponderTest extends TestCase
{
    /**
     * A mock of a [Responder] service class.
     *
     * @var \Mockery\MockInterface
     */
    protected $responder;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->responder = Mockery::mock(ResponderContract::class);
        $this->app->instance(ResponderContract::class, $this->responder);
    }

    /**
     * Assert that the parameters sent to the [error] method is forwarded to the
     * responder service.
     */
    public function testErrorMethodShouldCallOnResponder(): void
    {
        $this->responder->shouldReceive('error')->andReturn($responseBuilder = $this->mockErrorResponseBuilder());

        $result = Responder::error($error = 'error_occured');

        $this->assertSame($responseBuilder, $result);
        $this->responder->shouldHaveReceived('error')->with($error)->once();
    }

    /**
     * Assert that the parameters sent to the [success] method is forwarded to the
     * responder service.
     */
    public function testSuccessMethodShouldCallOnResponder(): void
    {
        $this->responder->shouldReceive('success')->andReturn($responseBuilder = $this->mockSuccessResponseBuilder());

        $result = Responder::success($data = ['foo' => 1]);

        $this->assertSame($responseBuilder, $result);
        $this->responder->shouldHaveReceived('success')->with($data)->once();
    }
}
