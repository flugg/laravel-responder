<?php

namespace Flugg\Responder\Tests\Unit\Http\Responses;

use Flugg\Responder\Http\Responses\Decorators\StatusCodeDecorator;
use Flugg\Responder\Tests\TestCase;

/**
 * Unit tests for the [Flugg\Responder\Http\SuccessResponseBuilder] class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class StatusCodeDecoratorTest extends TestCase
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
     * Test that the [make] method decorates the response data with info about status code.
     */
    public function testMakeMethodShouldAppendStatusCodeToResponseData()
    {
        $data     = ['foo' => 1]; 
        $status   = 201;
        $response = $this->responseDecorator->make($data, $status);

        $this->assertEquals(json_encode(array_merge(['status' => $status], $data)), $response->getContent());
    }
}