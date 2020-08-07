<?php

namespace Flugg\Responder\Tests\Unit;

use Flugg\Responder\Http\Builders\ErrorResponseBuilder;
use Flugg\Responder\Http\Builders\SuccessResponseBuilder;
use Flugg\Responder\Responder;
use Flugg\Responder\Tests\UnitTestCase;

/**
 * Unit tests for the [Flugg\Responder\Responder] class.
 *
 * @see \Flugg\Responder\Responder
 */
class ResponderTest extends UnitTestCase
{
    /**
     * Mock of a success response builder.
     *
     * @var \Mockery\MockInterface|\Flugg\Responder\Http\Builders\SuccessResponseBuilder
     */
    protected $successResponseBuilder;

    /**
     * Mock of an error response builder.
     *
     * @var \Mockery\MockInterface|\Flugg\Responder\Http\Builders\ErrorResponseBuilder
     */
    protected $errorResponseBuilder;

    /**
     * Class being tested.
     *
     * @var \Flugg\Responder\Responder
     */
    protected $responder;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->successResponseBuilder = mock(SuccessResponseBuilder::class);
        $this->errorResponseBuilder = mock(ErrorResponseBuilder::class);
        $this->responder = new Responder($this->successResponseBuilder, $this->errorResponseBuilder);
    }

    /**
     * Assert that the parameters sent to [success] is forwarded to the success response builder.
     */
    public function testSuccessMethodShouldCallOnSuccessResponseBuilder()
    {
        $this->successResponseBuilder->allows('make')->andReturnSelf();
        $result = $this->responder->success($data = ['foo' => 1]);

        $this->assertSame($this->successResponseBuilder, $result);
        $this->successResponseBuilder->shouldHaveReceived('make')->with($data);
    }

    /**
     * Assert that the parameters sent to [error] is forwarded to the error response builder.
     */
    public function testErrorMethodShouldCallOnErrorResponseBuilder()
    {
        $this->errorResponseBuilder->allows('make')->andReturnSelf();
        $result = $this->responder->error($error = 'error_occured', $message = 'An error has occured.');

        $this->assertSame($this->errorResponseBuilder, $result);
        $this->errorResponseBuilder->shouldHaveReceived('make')->with($error, $message);
    }
}
