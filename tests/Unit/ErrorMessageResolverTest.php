<?php

namespace Flugg\Responder\Tests\Unit;

use Flugg\Responder\ErrorMessageResolver;
use Flugg\Responder\Tests\TestCase;
use Illuminate\Contracts\Translation\Translator;
use Mockery;

/**
 * Unit tests for the [Flugg\Responder\ErrorMessageResolver] class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class ErrorMessageResolverTest extends TestCase
{
    /**
     * A translator mock.
     *
     * @var \Mockery\MockInterface
     */
    protected $translator;

    /**
     * The error message resolver.
     *
     * @var \Flugg\Responder\ErrorMessageResolver
     */
    protected $messageResolver;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->translator = Mockery::mock(Translator::class);
        $this->messageResolver = new ErrorMessageResolver($this->translator);
    }

    /**
     * Test that the [resolve] method should use the Laravel translator to resolve a message.
     */
    public function testResolveMethodShouldFindMessageUsingTheTranslator()
    {
        $this->translator->shouldReceive('has')->andReturn(true);
        $this->translator->shouldReceive('trans')->andReturn($message = 'A test error has occured.');

        $result = $this->messageResolver->resolve($code = 'test_error');

        $this->assertEquals($message, $result);
        $this->translator->shouldHaveReceived('has')->with("errors.$code")->once();
        $this->translator->shouldHaveReceived('trans')->with("errors.$code")->once();
    }

    /**
     * Test that the [resolve] method should use the Laravel translator to resolve a message.
     */
    public function testResolveMethodReturnsNullIfTranslatorKeyIsNotSet()
    {
        $this->translator->shouldReceive('has')->andReturn(false);

        $message = $this->messageResolver->resolve('test_error');

        $this->assertEquals(null, $message);
    }
}