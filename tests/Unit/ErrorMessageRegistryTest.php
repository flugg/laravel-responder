<?php

namespace Flugg\Responder\Tests\Unit;

use Flugg\Responder\ErrorMessageRegistry;
use Flugg\Responder\Tests\UnitTestCase;
use Illuminate\Contracts\Translation\Translator;
use Mockery\MockInterface;

/**
 * Unit tests for the [Flugg\Responder\ErrorMessageRegistry] class.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class ErrorMessageRegistryTest extends UnitTestCase
{
    /**
     * Class being tested.
     *
     * @var ErrorMessageRegistry
     */
    protected $messageRegistry;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->messageRegistry = new ErrorMessageRegistry();
    }

    /**
     * Assert that the [resolve] method resolves error message registered using the [register] method.
     */
    public function testResolveMethodShouldResolveMessageFromRegister()
    {
        $this->messageRegistry->register($errorCode = 'error_occured', $message = 'An error has occured.');

        $result = $this->messageRegistry->resolve($errorCode);

        $this->assertEquals($message, $result);
    }

    /**
     * Assert that the [register] method accepts an array of error messages to register.
     */
    public function testRegisterMethodAllowsSettingMultipleMessages()
    {
        $this->messageRegistry->register($messages = [
            'error_occured' => 'An error has occured.',
            'another_error_occured' => 'Yet another error occured.',
        ]);

        foreach ($messages as $errorCode => $message) {
            $result = $this->messageRegistry->resolve($errorCode);
            $this->assertEquals($message, $result);
        }
    }

    /**
     * Assert that the [resolve] method returns null if no error messages are registered.
     */
    public function testResolveMethodReturnsNullIfNoMessageIsFound()
    {
        $message = $this->messageRegistry->resolve('error_occured');

        $this->assertNull($message);
    }
}
