<?php

namespace Flugg\Responder\Tests\Unit\Http\Responses;

use Flugg\Responder\Http\Responses\Decorators\SuccessFlagDecorator;
use Flugg\Responder\Tests\TestCase;

/**
 * Unit tests for the [Flugg\Responder\Http\Decorators\SuccessFlagDecorator] class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
final class SuccessFlagDecoratorTest extends TestCase
{
    /**
     * A mock of a [ResponseFactory] class.
     *
     * @var \Mockery\MockInterface
     */
    protected $responseFactory;

    /**
     * The [SuccessFlagDecorator] class being tested.
     *
     * @var \Flugg\Responder\Http\Responses\Decorators\SuccessFlagDecorator
     */
    protected $responseDecorator;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->responseFactory = $this->mockResponseFactory();
        $this->responseDecorator = new SuccessFlagDecorator($this->responseFactory);
    }

    /**
     * Assert that the [make] method decorates the response data with information about
     * whether or not the response was successful.
     */
    public function testMakeMethodShouldAppendSuccessFlagFieldToResponseData(): void
    {
        $response = $this->responseDecorator->make($data = ['foo' => 1], $status = 201);

        $this->assertEquals(json_encode(array_merge(['success' => true], $data)), $response->getContent());
    }
}
