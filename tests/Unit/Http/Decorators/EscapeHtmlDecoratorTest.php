<?php

namespace Flugg\Responder\Tests\Unit\Http\Responses;

use Flugg\Responder\Contracts\Http\ResponseFactory;
use Flugg\Responder\Http\Decorators\EscapeHtmlDecorator;
use Flugg\Responder\Tests\UnitTestCase;
use Illuminate\Http\JsonResponse;
use Mockery\MockInterface;

/**
 * Unit tests for the [Flugg\Responder\Http\Decorators\EscapeHtmlDecorator] class.
 *
 * @package flugger/laravel-responder
 * @author Paolo Caleffi <p.caleffi@dreamonkey.com>
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class EscapeHtmlDecoratorTest extends UnitTestCase
{
    /**
     * A mock of a response factory.
     *
     * @var MockInterface
     */
    protected $responseFactory;

    /**
     * The decorator class being tested.
     *
     * @var EscapeHtmlDecorator
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
     * Assert that the [make] method decorates the response data by escaping any HTML tags.
     */
    public function testMakeMethodEscapesHtmlTagsInResponseData()
    {
        $response = $this->responseDecorator->make(['foo' => $html = '<html></html>'], 200);

        $this->assertEquals(['foo' => e($html)], $response->getData(true));
    }
}
