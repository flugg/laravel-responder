<?php

namespace Flugg\Responder\Tests\Unit;

use Flugg\Responder\Contracts\ErrorSerializer;
use Flugg\Responder\ErrorFactory;
use Flugg\Responder\ErrorMessageResolver;
use Flugg\Responder\Tests\TestCase;
use Mockery;

/**
 * Unit tests for the [Flugg\Responder\ErrorFactory] class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class ErrorFactoryTest extends TestCase
{
    /**
     * The error message resolver mock.
     *
     * @var \Mockery\MockInterface
     */
    protected $messageResolver;

    /**
     * The error serializer mock.
     *
     * @var \Mockery\MockInterface
     */
    protected $serializer;

    /**
     * The error factory.
     *
     * @var \Flugg\Responder\ErrorFactory
     */
    protected $factory;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->messageResolver = Mockery::mock(ErrorMessageResolver::class);
        $this->serializer = Mockery::mock(ErrorSerializer::class);
        $this->factory = new ErrorFactory($this->messageResolver, $this->serializer);
    }

    /**
     * Test that the [make] method uses the error serializer to serialize the error data.
     */
    public function testMakeMethodSerializesErrorDataUsingTheSerializer()
    {
        [$code, $message, $data] = ['test_error', 'A test error has occured.', ['foo' => 1]];
        $this->serializer->shouldReceive('format')->andReturn($error = ['bar' => 2]);

        $result = $this->factory->make($code, $message, $data);

        $this->assertEquals($error, $result);
        $this->serializer->shouldHaveReceived('format')->with($code, $message, $data)->once();
    }

    /**
     * Test that the [make] method resolves a message using the error message resolver when
     * none is given.
     */
    public function testMakeMethodShouldResolveMessageFromMessageResolver()
    {
        $this->serializer->shouldReceive('format')->andReturn([]);
        $this->messageResolver->shouldReceive('resolve')->andReturn($message = 'A test error has occured.');

        $this->factory->make($code = 'test_error');

        $this->serializer->shouldHaveReceived('format')->with($code, $message, null)->once();
        $this->messageResolver->shouldHaveReceived('resolve')->with($code)->once();
    }

    /**
     * Test that the [make] method allows skipping all parameters returning a null error.
     */
    public function testMakeMethodAllowsSkippingErrorCodeParameter()
    {
        $this->serializer->shouldReceive('format')->andReturn($error = ['foo' => 1]);

        $result = $this->factory->make();

        $this->assertEquals($error, $result);
    }
}