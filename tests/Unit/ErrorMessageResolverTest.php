<?php

namespace Flugg\Responder\Tests\Unit;

use Flugg\Responder\ErrorMessageResolver;
use Flugg\Responder\Tests\TestCase;
use Illuminate\Translation\Translator;
use Mockery;

/**
 * Unit tests for the [Flugg\Responder\ErrorMessageResolver] class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
final class ErrorMessageResolverTest extends TestCase
{
    /**
     * A mock of a [Translator] service class.
     *
     * @var \Mockery\MockInterface
     */
    protected $translator;

    /**
     * The [ErrorMessageResolver] class being tested.
     *
     * @var \Flugg\Responder\ErrorMessageResolver
     */
    protected $messageResolver;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->translator = Mockery::mock(Translator::class);
        $this->messageResolver = new ErrorMessageResolver($this->translator);
    }

    /**
     * Assert that the [resolve] method resolves error message from cache if binding is registered.
     */
    public function testResolveMethodShouldResolveMessageFromCacheIfSet(): void
    {
        $this->messageResolver->register($errorCode = 'test_error', $message = 'A test error occured.');

        $result = $this->messageResolver->resolve($errorCode);

        $this->assertEquals($message, $result);
    }

    /**
     * Assert that the [resolve] method uses the translator to resolve a message.
     */
    public function testResolveMethodShouldResolveMessageFromTranslator(): void
    {
        $this->translator->shouldReceive('has')->andReturn(true);
        $this->translator->shouldReceive('get')->andReturn($message = 'A test error has occured.');

        $result = $this->messageResolver->resolve($code = 'test_error');

        $this->assertEquals($message, $result);
        $this->translator->shouldHaveReceived('has')->with("errors.$code")->once();
        $this->translator->shouldHaveReceived('get')->with("errors.$code")->once();
    }

    /**
     * Assert that the [resolve] method returns [null] if the translator can't resolve message.
     */
    public function testResolveMethodReturnsNullIfNoTranslatorKeyIsSet(): void
    {
        $this->translator->shouldReceive('has')->andReturn(false);

        $message = $this->messageResolver->resolve('test_error');

        $this->assertNull($message);
    }
}