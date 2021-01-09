<?php

namespace Flugg\Responder\Tests\Unit;

use Flugg\Responder\Http\Builders\ErrorResponseBuilder;
use Flugg\Responder\Http\Builders\SuccessResponseBuilder;
use Flugg\Responder\Responder;
use Flugg\Responder\Tests\UnitTestCase;

/**
 * Unit tests for the [Responder] class.
 *
 * @see \Flugg\Responder\Responder
 */
class ResponderTest extends UnitTestCase
{
    /**
     * Mock of a [\Flugg\Responder\Http\Builders\SuccessResponseBuilder] class.
     *
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    protected $successResponseBuilder;

    /**
     * Mock of a [\Flugg\Responder\Http\Builders\ErrorResponseBuilder] class.
     *
     * @var \Prophecy\Prophecy\ObjectProphecy
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
    protected function setUp(): void
    {
        parent::setUp();

        $this->successResponseBuilder = $this->prophesize(SuccessResponseBuilder::class);
        $this->errorResponseBuilder = $this->prophesize(ErrorResponseBuilder::class);
        $this->responder = new Responder($this->successResponseBuilder->reveal(), $this->errorResponseBuilder->reveal());
    }

    /**
     * Assert that the parameters sent to [success] is forwarded to the success response builder.
     */
    public function testSuccessMethodForwardsCallToSuccessResponseBuilder()
    {
        $data = ['foo' => 1];
        $this->successResponseBuilder->make($data)->willReturn($this->successResponseBuilder);

        $result = $this->responder->success($data);

        $this->assertSame($this->successResponseBuilder->reveal(), $result);
    }

    /**
     * Assert that the parameters sent to [error] is forwarded to the error response builder.
     */
    public function testErrorMethodForwardsCallToErrorResponseBuilder()
    {
        [$error, $message] = ['error_occurred', 'An error has occurred.'];
        $this->errorResponseBuilder->make($error, $message)->willReturn($this->errorResponseBuilder);

        $result = $this->responder->error($error, $message);

        $this->assertSame($this->errorResponseBuilder->reveal(), $result);
    }
}
