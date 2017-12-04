<?php

namespace Flugg\Responder\Tests\Unit\Http\Responses;

use Flugg\Responder\Http\Responses\Decorators\StatusCodeDecorator;
use Flugg\Responder\Tests\TestCase;

/**
 * Unit tests for the [Flugg\Responder\Http\Decorators\StatusCodeDecorator] class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class StatusCodeDecoratorTest extends TestCase
{
    /**
     * A mock of a [ResponseFactory] class.
     *
     * @var \Mockery\MockInterface
     */
    protected $responseFactory;

    /**
     * The [StatusCodeDecorator] class being tested.
     *
     * @var \Flugg\Responder\Http\Responses\Decorators\StatusCodeDecorator
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
        $this->responseDecorator = new StatusCodeDecorator($this->responseFactory);
    }

    /**
     * Assert that the [make] method decorates the response data with information about
     * status code.
     */
    public function testMakeMethodShouldAppendStatusCodeFieldToResponseData()
    {
        $response = $this->responseDecorator->make($data = ['foo' => 1], $status = 201);

        $this->assertEquals(json_encode(array_merge(['status' => $status], $data)), $response->getContent());
    }
}