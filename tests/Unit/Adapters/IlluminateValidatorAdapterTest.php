<?php

namespace Flugg\Responder\Tests\Unit\Adapters;

use Flugg\Responder\Adapters\IlluminateValidatorAdapter;
use Flugg\Responder\Tests\UnitTestCase;
use Illuminate\Contracts\Support\MessageBag;
use Illuminate\Contracts\Validation\Validator;

/**
 * Unit tests for the [IlluminateValidatorAdapter] class.
 *
 * @see \Flugg\Responder\Adapters\IlluminateValidatorAdapter
 */
class IlluminateValidatorAdapterTest extends UnitTestCase
{
    /**
     * Mock of an [\Illuminate\Contracts\Validation\Validator] class.
     *
     * @var \Prophecy\Prophecy\ObjectProphecy
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

        $this->validator = $this->prophesize(Validator::class);
        $this->adapter = new IlluminateValidatorAdapter($this->validator->reveal());
    }

    /**
     * Assert that [failed] returns a list of fields that failed validation.
     */
    public function testFailedMethodReturnsFailedFields()
    {
        $this->validator->failed()->willReturn($failed = [
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
        $this->validator->failed()->willReturn([
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
        $messageBag = $this->prophesize(MessageBag::class);
        $messageBag->get('foo')->willReturn([$minMessage = 'Must be larger', $emailMessage = 'Invalid email']);
        $messageBag->get('bar.baz')->willReturn([$requiredMessage = 'Required field']);
        $this->validator->failed()->willReturn([
            'foo' => ['Min' => [10], 'Email' => []],
            'bar.baz' => ['Required' => []],
        ]);
        $this->validator->errors()->willReturn($messageBag);

        $this->assertEquals([
            'foo.min' => $minMessage,
            'foo.email' => $emailMessage,
            'bar.baz.required' => $requiredMessage,
        ], $this->adapter->messages());
    }
}
