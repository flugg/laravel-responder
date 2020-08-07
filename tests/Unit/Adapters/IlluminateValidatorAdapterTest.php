<?php

namespace Flugg\Responder\Tests\Unit\Adapters;

use Flugg\Responder\Adapters\IlluminateValidatorAdapter as AdaptersIlluminateValidatorAdapter;
use Flugg\Responder\Tests\UnitTestCase;
use Illuminate\Contracts\Support\MessageBag;
use Illuminate\Contracts\Validation\Validator;

/**
 * Unit tests for the [Flugg\Responder\Adapters\IlluminateValidatorAdapter] class.
 *
 * @see \Flugg\Responder\Adapters\IlluminateValidatorAdapter
 */
class IlluminateValidatorAdapterTest extends UnitTestCase
{
    /**
     * Mock of an Illuminate validator.
     *
     * @var \Mockery\MockInterface|\Illuminate\Contracts\Validation\Validator
     */
    protected $validator;

    /**
     * Class being tested.
     *
     * @var \Flugg\Responder\Adapters\IlluminateValidatorAdapter
     */
    protected $adapter;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->validator = mock(Validator::class);
        $this->adapter = new AdaptersIlluminateValidatorAdapter($this->validator);
    }

    /**
     * Assert that [failed] returns a list of fields that failed validation.
     */
    public function testFailedMethodReturnsFailedFields()
    {
        $this->validator->allows('failed')->andReturn($failed = [
            'foo' => [],
            'bar.baz' => [],
        ]);

        $this->assertEquals(array_keys($failed), $this->adapter->failed());
    }

    /**
     * Assert that [errors] returns a map of fields mapped to a list of failed rules.
     */
    public function testErrorsMethodReturnsMapOfFailedRules()
    {
        $this->validator->allows('failed')->andReturn([
            'foo' => ['Min' => [10], 'Email' => []],
            'bar.baz' => ['Required' => []],
        ]);

        $this->assertEquals([
            'foo' => ['min', 'email'],
            'bar.baz' => ['required'],
        ], $this->adapter->errors());
    }

    /**
     * Assert that [messages] returns a list of fields and rules mapped to corresponding messages.
     */
    public function testMessagesMethodReturnsMapOfValidationMessages()
    {
        $this->validator->allows('failed')->andReturn([
            'foo' => ['Min' => [10], 'Email' => []],
            'bar.baz' => ['Required' => []],
        ]);
        $this->validator->allows('errors')->andReturn($messageBag = mock(MessageBag::class));
        $messageBag->allows('get')->with('foo')->andReturn([$minMessage = 'Must be larger', $emailMessage = 'Invalid email']);
        $messageBag->allows('get')->with('bar.baz')->andReturn([$requiredMessage = 'Required field']);

        $this->assertEquals([
            'foo.min' => $minMessage,
            'foo.email' => $emailMessage,
            'bar.baz.required' => $requiredMessage,
        ], $this->adapter->messages());
    }
}
