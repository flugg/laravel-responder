<?php

namespace Flugg\Responder\Tests\Unit\Http\Decorators;

use Flugg\Responder\Contracts\Http\ResponseFactory;
use Flugg\Responder\Http\Decorators\EscapeHtmlDecorator;
use Flugg\Responder\Tests\UnitTestCase;
use Illuminate\Http\JsonResponse;

/**
 * Unit tests for the [Flugg\Responder\Http\Decorators\EscapeHtmlDecorator] class.
 *
 * @see \Flugg\Responder\Http\Decorators\EscapeHtmlDecorator
 */
class EscapeHtmlDecoratorTest extends UnitTestCase
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
     * @var \Flugg\Responder\Http\Decorators\EscapeHtmlDecorator
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
        $this->responseDecorator = new EscapeHtmlDecorator($this->responseFactory);

        $this->responseFactory->allows('make')->andReturnUsing(function ($data, $status, $headers) {
            return new JsonResponse($data, $status, $headers);
        });
    }

    /**
     * Assert that [make] decorates the response data by escaping any HTML tags.
     */
    public function testMakeMethodEscapesHtmlTags()
    {
        $response = $this->responseDecorator->make(['foo' => $html = '<html></html>'], 200);

        $this->assertEquals(['foo' => e($html)], $response->getData(true));
    }
}
