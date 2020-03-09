<?php

namespace Flugg\Responder\Tests\Unit;

use Flugg\Responder\Http\ErrorMessageResolver;
use Flugg\Responder\Tests\UnitTestCase;
use Illuminate\Contracts\Translation\Translator;
use Mockery\MockInterface;

/**
 * Unit tests for the [Flugg\Responder\Http\ErrorMessageResolver] class.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class ErrorMessageResolverTest extends UnitTestCase
{
    /**
     * A mock of a translator service.
     *
     * @var MockInterface|Translator
     */
    protected $translator;

    /**
     * The class being tested.
     *
     * @var ErrorMessageResolver
     */
    protected $messageResolver;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->translator = mock(Translator::class);
        $this->messageResolver = new ErrorMessageResolver($this->translator);
    }

    /**
     * Assert that the [resolve] method resolves error message registered using the [register] method.
     */
    public function testResolveMethodShouldResolveMessageFromRegister()
    {
        $this->messageResolver->register($errorCode = 'error_occured', $message = 'An error has occured.');

        $result = $this->messageResolver->resolve($errorCode);

        $this->assertEquals($message, $result);
    }

    /**
     * Assert that the [register] method accepts an array of error messages to register.
     */
    public function testRegisterMethodAllowsSettingMultipleMessages()
    {
        $this->messageResolver->register($messages = [
            'error_occured' => 'An error has occured.',
            'another_error_occured' => 'Yet another error occured.',
        ]);

        foreach ($messages as $errorCode => $message) {
            $result = $this->messageResolver->resolve($errorCode);
            $this->assertEquals($message, $result);
        }
    }

    /**
     * Assert that the [resolve] method uses a translator to resolve messages.
     */
    public function testResolveMethodShouldResolveMessageFromTranslator()
    {
        $this->translator->allows('get')->andReturn($message = 'An error has occured.');

        $result = $this->messageResolver->resolve($code = 'error_occured');

        $this->assertEquals($message, $result);
        $this->translator->shouldHaveReceived('get')->with("errors.$code");
    }

    /**
     * Assert that the [resolve] method returns null if no error messages are registered.
     */
    public function testResolveMethodReturnsNullIfNoMessageIsFound()
    {
        $this->translator->allows('get')->andReturn(null);

        $message = $this->messageResolver->resolve('error_occured');

        $this->assertNull($message);
    }
}
