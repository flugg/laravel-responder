<?php

namespace Flugg\Responder\Tests\Unit\Http\Responses;

use Flugg\Responder\Contracts\Http\ResponseFactory;
use Flugg\Responder\Http\Decorators\PrettyPrintDecorator;
use Flugg\Responder\Tests\UnitTestCase;
use Illuminate\Http\JsonResponse;

/**
 * Unit tests for the [Flugg\Responder\Http\Decorators\PrettyPrintDecorator] class.
 *
 * @package flugger/laravel-responder
 * @author Paolo Caleffi <p.caleffi@dreamonkey.com>
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class PrettyPrintDecoratorTest extends UnitTestCase
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
     * @var PrettyPrintDecorator
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
     * Assert that the [make] method decorates the response data by encoding JSON with the [JSON_PRETTY_PRINT] option.
     */
    public function testMakeMethodPrettifiesJsonResponseData()
    {
        $response = $this->responseDecorator->make($data = ['foo' => ['bar', 'baz' => 1]], 200);

        $this->assertEquals(json_encode($data, JSON_PRETTY_PRINT), $response->getContent());
    }
}
