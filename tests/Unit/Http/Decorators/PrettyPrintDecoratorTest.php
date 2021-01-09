<?php

namespace Flugg\Responder\Tests\Unit\Http\Decorators;

use Flugg\Responder\Contracts\Http\ResponseFactory;
use Flugg\Responder\Http\Decorators\PrettyPrintDecorator;
use Flugg\Responder\Tests\UnitTestCase;
use Illuminate\Http\JsonResponse;

/**
 * Unit tests for the [PrettyPrintDecorator] class.
 *
 * @see \Flugg\Responder\Http\Decorators\PrettyPrintDecorator
 */
class PrettyPrintDecoratorTest extends UnitTestCase
{
    /**
     * Mock of a [\Flugg\Responder\Contracts\Http\ResponseFactory] interface.
     *
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    protected $responseFactory;

    /**
     * Class being tested.
     *
     * @var \Flugg\Responder\Http\Decorators\PrettyPrintDecorator
     */
    protected $responseDecorator;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->responseFactory = $this->prophesize(ResponseFactory::class);
        $this->responseDecorator = new PrettyPrintDecorator($this->responseFactory->reveal());
    }

    /**
     * Assert that [make] decorates the response data by encoding JSON with the [JSON_PRETTY_PRINT] option.
     */
    public function testMakeMethodPrettifiesJson()
    {
        $this->responseFactory->make($data = ['foo' => ['bar', 'baz' => 1]], $status = 200, [])->will(function ($args) {
            return new JsonResponse($args[0]);
        });

        $response = $this->responseDecorator->make($data, $status);

        $this->assertEquals(json_encode($data, JSON_PRETTY_PRINT), $response->getContent());
    }
}
