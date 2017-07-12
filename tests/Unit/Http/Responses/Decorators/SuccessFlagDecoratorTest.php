<?php

namespace Flugg\Responder\Tests\Unit\Http\Responses;

use Flugg\Responder\Http\Responses\Decorators\SuccessFlagDecorator;
use Flugg\Responder\Tests\TestCase;

/**
 * Unit tests for the [Flugg\Responder\Http\SuccessResponseBuilder] class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class SuccessFlagDecoratorTest extends TestCase
{
    /**
     * The response factory mock.
     *
     * @var \Mockery\MockInterface
     */
    protected $responseFactory;

    /**
     * The status code response decorator.
     *
     * @var \Flugg\Responder\Http\Responses\Decorators\SuccessFlagDecorator
     */
    protected $responseDecorator;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->responseFactory = $this->mockResponseFactory();
        $this->responseDecorator = new SuccessFlagDecorator($this->responseFactory);
    }

    /**
     * Test that the [make] method decorates the response data with info about status code.
     */
    public function testMakeMethodShouldAppendSuccessFlagToResponseData()
    {
        [$data, $status] = [['foo' => 1], 201];

        $response = $this->responseDecorator->make($data, $status);

        $this->assertEquals(json_encode(array_merge(['success' => true], $data)), $response->getContent());
    }
}
