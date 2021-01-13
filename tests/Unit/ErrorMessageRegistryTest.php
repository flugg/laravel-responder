<?php

namespace Flugg\Responder\Tests\Unit;

use Flugg\Responder\ErrorMessageRegistry;
use Flugg\Responder\Tests\UnitTestCase;

/**
 * Unit tests for the [ErrorMessageRegistry] class.
 *
 * @see \Flugg\Responder\ErrorMessageRegistry
 */
class ErrorMessageRegistryTest extends UnitTestCase
{
    /**
     * Class being tested.
     *
     * @var \Flugg\Responder\ErrorMessageRegistry
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

        $this->messageRegistry = new ErrorMessageRegistry;
    }

    /**
     * Assert that [resolve] returns registered error message.
     */
    public function testResolveMethodReturnsMessage()
    {
        $this->messageRegistry->register($code = 'error_occurred', $message = 'An error has occurred.');

        $result = $this->messageRegistry->resolve($code);

        $this->assertSame($message, $result);
    }

    /**
     * Assert that [resolve] returns null if no error messages are registered.
     */
    public function testResolveMethodReturnsNull()
    {
        $result = $this->messageRegistry->resolve('error_occurred');

        $this->assertNull($result);
    }

    /**
     * Assert that [register] accepts an array of error messages.
     */
    public function testRegisterMethodCanSetMultipleMessages()
    {
        $this->messageRegistry->register($messages = [
            'error_occurred' => 'An error has occurred.',
            'another_error_occurred' => 'Yet another error occurred.',
        ]);

        foreach ($messages as $code => $message) {
            $result = $this->messageRegistry->resolve($code);

            $this->assertSame($message, $result);
        }
    }
}
