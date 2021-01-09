<?php

namespace Flugg\Responder\Tests\Unit\Http\Decorators;

use Flugg\Responder\Contracts\Http\ResponseFactory;
use Flugg\Responder\Http\Decorators\EscapeHtmlDecorator;
use Flugg\Responder\Tests\UnitTestCase;
use Illuminate\Http\JsonResponse;

/**
 * Unit tests for the [EscapeHtmlDecorator] class.
 *
 * @see \Flugg\Responder\Http\Decorators\EscapeHtmlDecorator
 */
class EscapeHtmlDecoratorTest extends UnitTestCase
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
     * @var \Flugg\Responder\Http\Decorators\EscapeHtmlDecorator
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

        $this->responseFactory = $this->prophesize(ResponseFactory::class);
        $this->responseDecorator = new EscapeHtmlDecorator($this->responseFactory->reveal());
    }

    /**
     * Assert that [make] decorates the response data by escaping HTML tags.
     */
    public function testMakeMethodEscapesHtmlTags()
    {
        $this->responseFactory->make(['foo' => e($html = '<html></html>')], $status = 200, [])->will(function ($args) {
            return new JsonResponse($args[0]);
        });

        $response = $this->responseDecorator->make(['foo' => $html = '<html></html>'], $status);

        $this->assertSame(['foo' => e($html)], $response->getData(true));
    }
}
