<?php

namespace Flugg\Responder\Tests\Unit\Http\Responses;

use Flugg\Responder\Contracts\AdapterFactory;
use Flugg\Responder\Contracts\Http\ErrorMessageResolver;
use Flugg\Responder\Contracts\Http\ResponseFactory;
use Flugg\Responder\Contracts\Http\ResponseFormatter;
use Flugg\Responder\Http\Builders\ErrorResponseBuilder;
use Flugg\Responder\Tests\UnitTestCase;
use Mockery\MockInterface;

/**
 * Unit tests for the [Flugg\Responder\Http\Builders\ErrorResponseBuilder] class.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class ErrorResponseBuilderTest extends UnitTestCase
{
    /**
     * A mock of a response factory.
     *
     * @var MockInterface|ResponseFactory
     */
    protected $responseFactory;

    /**
     * A mock of an adapter factory.
     *
     * @var AdapterFactory|MockInterface
     */
    protected $adapterFactory;

    /**
     * A mock of an error message resolver.
     *
     * @var ErrorMessageResolver|MockInterface
     */
    protected $messageResolver;

    /**
     * A mock of a response formatter.
     *
     * @var MockInterface|ResponseFormatter
     */
    protected $formatter;

    /**
     * The builder class being tested.
     *
     * @var \Flugg\Responder\Http\Responses\ErrorResponseBuilder
     */
    protected $responseBuilder;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->responseFactory = mock(ResponseFactory::class);
        $this->adapterFactory = mock(AdapterFactory::class);
        $this->messageResolver = mock(ErrorMessageResolver::class);
        $this->responseBuilder = new ErrorResponseBuilder($this->responseFactory, $this->adapterFactory, $this->messageResolver);
    }
}
