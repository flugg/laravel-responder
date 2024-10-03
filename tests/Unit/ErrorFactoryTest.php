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
final class ErrorFactoryTest extends TestCase
{
    /**
     * A mock of an [ErrorMessageResolver] class.
     *
     * @var \Mockery\MockInterface
     */
    protected $messageResolver;

    /**
     * A mock of an [ErrorSerializer] class.
     *
     * @var \Mockery\MockInterface
     */
    protected $serializer;

    /**
     * The [ErrorFactory] class being tested.
     *
     * @var \Flugg\Responder\ErrorFactory
     */
    protected $factory;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->messageResolver = Mockery::mock(ErrorMessageResolver::class);
        $this->serializer = Mockery::mock(ErrorSerializer::class);
        $this->factory = new ErrorFactory($this->messageResolver);
    }

    /**
     * Assert that the [make] method uses the [ErrorSerializer] to serialize the error data.
     */
    public function testMakeMethodSerializesErrorDataUsingTheSerializer(): void
    {
        $this->serializer->shouldReceive('format')->andReturn($error = ['bar' => 2]);

        $result = $this->factory->make($this->serializer, $code = 'test_error', $message = 'A test error has occured.', $data = ['foo' => 1]);

        $this->assertEquals($error, $result);
        $this->serializer->shouldHaveReceived('format')->with($code, $message, $data)->once();
    }

    /**
     * Assert that the [make] method resolves a message using the [ErrorMessageResolver] when
     * none is given.
     */
    public function testMakeMethodShouldResolveMessageFromMessageResolver(): void
    {
        $this->serializer->shouldReceive('format')->andReturn([]);
        $this->messageResolver->shouldReceive('resolve')->andReturn($message = 'A test error has occured.');

        $this->factory->make($this->serializer, $code = 'test_error');

        $this->serializer->shouldHaveReceived('format')->with($code, $message, null)->once();
        $this->messageResolver->shouldHaveReceived('resolve')->with($code)->once();
    }

    /**
     * Assert that the [make] method allows skipping all parameters except serializer.
     */
    public function testMakeMethodAllowsPassingOnlySerializer(): void
    {
        $this->serializer->shouldReceive('format')->andReturn($error = ['foo' => 1]);

        $result = $this->factory->make($this->serializer);

        $this->assertEquals($error, $result);
    }
}
