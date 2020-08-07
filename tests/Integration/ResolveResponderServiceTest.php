<?php

namespace Flugg\Responder\Tests\Integration;

use Flugg\Responder\Contracts\Responder as ResponderContract;
use Flugg\Responder\Facades\Responder as ResponderFacade;
use Flugg\Responder\Http\Builders\ErrorResponseBuilder;
use Flugg\Responder\Http\Builders\SuccessResponseBuilder;
use Flugg\Responder\MakesJsonResponses;
use Flugg\Responder\Responder;
use Flugg\Responder\Tests\IntegrationTestCase;

/**
 * Integration tests for testing different ways of resolving the [Flugg\Responder\Responder] service.
 */
class ResolveResponderServiceTest extends IntegrationTestCase
{
    /**
     * Mock of a responder service.
     *
     * @var \Mockery\MockInterface|\Flugg\Responder\Contracts\Responder
     */
    protected $responder;

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
     * Mock of a trait for making JSON responses.
     *
     * @var \Mockery\MockInterface|\Flugg\Responder\MakesJsonResponses
     */
    protected $trait;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->app->instance(ResponderContract::class, $this->responder = mock(Responder::class));
        $this->responder->allows([
            'success' => $this->successResponseBuilder = mock(SuccessResponseBuilder::class),
            'error' => $this->errorResponseBuilder = mock(ErrorResponseBuilder::class),
        ]);
        $this->trait = $this->getMockForTrait(MakesJsonResponses::class);
    }

    /**
     * Assert that you can get access to the service through the service container and dependency injection.
     */
    public function testResolveFromContainer()
    {
        $result = $this->app->make(ResponderContract::class);

        $this->assertSame($this->responder, $result);
    }

    /**
     * Assert that you can get access to the service through a helper function.
     */
    public function testResolveFromHelperFunction()
    {
        $result = responder();

        $this->assertSame($this->responder, $result);
    }

    /**
     * Assert that calls you make on the facade is forwarded to the service.
     */
    public function testResolveFromFacade()
    {
        $successResult = ResponderFacade::success($data = ['foo' => 123]);
        $errorResult = ResponderFacade::error($code = 'error_occured', $message = 'An error has occured.');

        $this->assertSame($this->successResponseBuilder, $successResult);
        $this->assertSame($this->errorResponseBuilder, $errorResult);
        $this->responder->shouldHaveReceived('success')->with($data);
        $this->responder->shouldHaveReceived('error')->with($code, $message);
    }

    /**
     * Assert that calls you make with the trait is forwarded to the service.
     */
    public function testResolveFromTrait()
    {
        $successResult = $this->trait->success($data = ['foo' => 123]);
        $errorResult = $this->trait->error($code = 'error_occured', $message = 'An error has occured.');

        $this->assertSame($this->successResponseBuilder, $successResult);
        $this->assertSame($this->errorResponseBuilder, $errorResult);
        $this->responder->shouldHaveReceived('success')->with($data);
        $this->responder->shouldHaveReceived('error')->with($code, $message);
    }
}
