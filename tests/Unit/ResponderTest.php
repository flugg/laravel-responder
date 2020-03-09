<?php

namespace Flugg\Responder\Tests\Unit;

use Flugg\Responder\Contracts\Http\ErrorResponseBuilder;
use Flugg\Responder\Contracts\Http\SuccessResponseBuilder;
use Flugg\Responder\Responder;
use Flugg\Responder\Tests\UnitTestCase;
use Mockery\MockInterface;

/**
 * Unit tests for the [Flugg\Responder\Responder] class.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class ResponderTest extends UnitTestCase
{
    /**
     * A mock of a success response builder.
     *
     * @var MockInterface|SuccessResponseBuilder
     */
    protected $successResponseBuilder;

    /**
     * A mock of an error response builder.
     *
     * @var ErrorResponseBuilder|MockInterface
     */
    protected $errorResponseBuilder;

    /**
     * The service class being tested.
     *
     * @var Responder
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
     * Assert that the parameters sent to the [success] method is forwarded to the success response builder.
     */
    public function testSuccessMethodShouldCallOnSuccessResponseBuilder()
    {
        $this->successResponseBuilder->allows('data')->andReturnSelf();
        $result = $this->responder->success($data = ['foo' => 1]);

        $this->assertSame($this->successResponseBuilder, $result);
        $this->successResponseBuilder->shouldHaveReceived('data')->with($data);
    }

    /**
     * Assert that the parameters sent to the [error] method is forwarded to the error response builder.
     */
    public function testErrorMethodShouldCallOnErrorResponseBuilder()
    {
        $this->errorResponseBuilder->allows('error')->andReturnSelf();
        $result = $this->responder->error($error = 'error_occured', $message = 'An error has occured.');

        $this->assertSame($this->errorResponseBuilder, $result);
        $this->errorResponseBuilder->shouldHaveReceived('error')->with($error, $message);
    }
}
