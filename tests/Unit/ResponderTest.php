<?php

namespace Flugg\Responder\Tests\Unit;

use Flugg\Responder\Responder;
use Flugg\Responder\Tests\TestCase;
use Illuminate\Http\JsonResponse;

/**
 * Unit tests for the [Flugg\Responder\Responder] class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class ResponderTest extends TestCase
{
    /**
     * Mock of the error response builder.
     *
     * @var \Mockery\MockInterface
     */
    protected $errorResponseBuilder;

    /**
     * Mock of the success response builder.
     *
     * @var \Mockery\MockInterface
     */
    protected $successResponseBuilder;

    /**
     * The responder service.
     *
     * @var \Flugg\Responder\Responder
     */
    protected $responder;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->errorResponseBuilder = $this->mockErrorResponseBuilder();
        $this->successResponseBuilder = $this->mockSuccessResponseBuilder();
        $this->responder = new Responder($this->errorResponseBuilder, $this->successResponseBuilder);
    }

    /**
     * Test that the parameters sent to the [error] method is forwarded to the error response builder.
     */
    public function testErrorMethodShouldCallOnTheErrorResponseBuilder()
    {
        $error   = 'error_occured';
        $message = 'An error has occured.';
        $result  = $this->responder->error($error, $message);

        $this->assertSame($this->errorResponseBuilder, $result);
        $this->errorResponseBuilder->shouldHaveReceived('error')->with($error, $message)->once();
    }

    /**
     * Test that the parameters sent to the [success] method is forwarded to the success response builder.
     */
    public function testSuccessMethodShouldCallOnTheErrorResponseBuilder()
    {
        $data        = ['foo' => 1];
        $transformer = $this->mockTransformer();
        $resourceKey = 'foo';
        $result      = $this->responder->success($data, $transformer, $resourceKey);

        $this->assertSame($this->successResponseBuilder, $result);
        $this->successResponseBuilder->shouldHaveReceived('transform')->with($data, $transformer, $resourceKey)->once();
    }
}
