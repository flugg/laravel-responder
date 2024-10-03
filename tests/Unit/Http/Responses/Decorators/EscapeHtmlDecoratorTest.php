<?php

namespace Flugg\Responder\Tests\Unit\Http\Responses;

use Flugg\Responder\Http\Responses\Decorators\EscapeHtmlDecorator;
use Flugg\Responder\Tests\TestCase;

/**
 * Unit tests for the [Flugg\Responder\Http\Decorators\EscapeHtmlDecorator] class.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
final class EscapeHtmlDecoratorTest extends TestCase
{
    /**
     * A mock of a [ResponseFactory] class.
     *
     * @var \Mockery\MockInterface
     */
    protected $responseFactory;

    /**
     * The [EscapeHtmlDecorator] class being tested.
     *
     * @var \Flugg\Responder\Http\Responses\Decorators\EscapeHtmlDecorator
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
        $this->responseDecorator = new EscapeHtmlDecorator($this->responseFactory);
    }

    /**
     * Assert that the [make] method decorates the response data escaping any HTML tags.
     */
    public function testMakeMethodShouldEscapeHtmlTagsInResponseData(): void
    {
        $response = $this->responseDecorator->make($data = ['foo' => '<html></html>'], $status = 201);

        $this->assertEquals(['foo' => e('<html></html>')], $response->getData(true));
    }
}
