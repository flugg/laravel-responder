<?php

namespace Flugg\Responder\Tests\Unit;

use Flugg\Responder\Responder;
use Flugg\Responder\Tests\TestCase;

/**
 * Unit tests for the [Flugg\Responder\Responder] class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
final class ResponderTest extends TestCase
{
    /**
     * A mock of a [SuccessResponseBuilder] class.
     *
     * @var \Mockery\MockInterface
     */
    protected $successResponseBuilder;

    /**
     * A mock of an [ErrorResponseBuilder] class.
     *
     * @var \Mockery\MockInterface
     */
    protected $errorResponseBuilder;

    /**
     * The [Responder] service class being tested.
     *
     * @var \Flugg\Responder\Responder
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

        $this->successResponseBuilder = $this->mockSuccessResponseBuilder();
        $this->errorResponseBuilder = $this->mockErrorResponseBuilder();
        $this->responder = new Responder($this->successResponseBuilder, $this->errorResponseBuilder);
    }

    /**
     * Assert that the parameters sent to the [success] method is forwarded to the success
     * response builder.
     */
    public function testSuccessMethodShouldCallOnSuccessResponseBuilder(): void
    {
        $result = $this->responder->success($data = ['foo' => 1], $transformer = $this->mockTransformer(), $resourceKey = 'foo');

        $this->assertSame($this->successResponseBuilder, $result);
        $this->successResponseBuilder->shouldHaveReceived('transform')->with($data, $transformer, $resourceKey)->once();
    }

    /**
     * Assert that the parameters sent to the [error] method is forwarded to the error
     * response builder.
     */
    public function testErrorMethodShouldCallOnErrorResponseBuilder(): void
    {
        $error = 'error_occured';
        $message = 'An error has occured.';
        $result = $this->responder->error($error, $message);

        $this->assertSame($this->errorResponseBuilder, $result);
        $this->errorResponseBuilder->shouldHaveReceived('error')->with($error, $message)->once();
    }
}
