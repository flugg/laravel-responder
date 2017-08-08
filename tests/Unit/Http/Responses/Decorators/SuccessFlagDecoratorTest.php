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
     * A mock of a [ResponseFactory] class.
     *
     * @var \Mockery\MockInterface
     */
    protected $responseFactory;

    /**
     * The [StatusCodeResponseDecorator] class being tested.
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
     * Assert that the [make] method decorates the response data with information about
     * wether or not the response was successful.
     */
    public function testMakeMethodShouldAppendSuccessFlagFieldToResponseData()
    {
        $response = $this->responseDecorator->make($data = ['foo' => 1], $status = 201);

        $this->assertEquals(json_encode(array_merge(['success' => true], $data)), $response->getContent());
    }
}
