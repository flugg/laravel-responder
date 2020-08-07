<?php

namespace Flugg\Responder\Tests\Unit\Http\Decorators;

use Flugg\Responder\Contracts\Http\ResponseFactory;
use Flugg\Responder\Http\Decorators\PrettyPrintDecorator;
use Flugg\Responder\Tests\UnitTestCase;
use Illuminate\Http\JsonResponse;

/**
 * Unit tests for the [Flugg\Responder\Http\Decorators\PrettyPrintDecorator] class.
 *
 * @see \Flugg\Responder\Http\Decorators\PrettyPrintDecorator
 */
class PrettyPrintDecoratorTest extends UnitTestCase
{
    /**
     * Mock of a response factory.
     *
     * @var \Mockery\MockInterface|\Flugg\Responder\Contracts\Http\ResponseFactory
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

        $this->responseFactory = mock(ResponseFactory::class);
        $this->responseDecorator = new PrettyPrintDecorator($this->responseFactory);

        $this->responseFactory->allows('make')->andReturnUsing(function ($data, $status, $headers) {
            return new JsonResponse($data, $status, $headers);
        });
    }

    /**
     * Assert that [make] decorates the response data by encoding JSON with the [JSON_PRETTY_PRINT] option.
     */
    public function testMakeMethodPrettifiesJson()
    {
        $response = $this->responseDecorator->make($data = ['foo' => ['bar', 'baz' => 1]], 200);

        $this->assertEquals(json_encode($data, JSON_PRETTY_PRINT), $response->getContent());
    }
}
