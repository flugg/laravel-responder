<?php

namespace Flugg\Responder\Tests\Unit\Http\Responses;

use Flugg\Responder\Http\Responses\Decorators\PrettyPrintDecorator;
use Flugg\Responder\Tests\TestCase;

/**
 * Unit tests for the [Flugg\Responder\Http\Decorators\PrettyPrintDecorator] class.
 *
 * @package flugger/laravel-responder
 * @author  Paolo Caleffi <p.caleffi@dreamonkey.com>
 * @license The MIT License
 */
class PrettyPrintDecoratorTest extends TestCase
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
     * @var \Flugg\Responder\Http\Responses\Decorators\PrettyPrintDecorator
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
        $this->responseDecorator = new PrettyPrintDecorator($this->responseFactory);
    }

    /**
     * Assert that the [make] method decorates the response data setting the pretty print JSON option.
     */
    public function testMakeMethodShouldPrettyPrintResponseData()
    {
        $response = $this->responseDecorator->make($data = ['foo' => ['bar', 'baz' => 1]], $status = 201);

        $this->assertEquals(json_encode($data, JSON_PRETTY_PRINT), $response->getContent());
    }
}
