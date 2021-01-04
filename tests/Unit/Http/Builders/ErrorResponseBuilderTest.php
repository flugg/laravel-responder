<?php

namespace Flugg\Responder\Tests\Unit\Http\Builders;

use Flugg\Responder\Contracts\AdapterFactory;
use Flugg\Responder\Contracts\ErrorMessageRegistry;
use Flugg\Responder\Contracts\Http\Formatter;
use Flugg\Responder\Contracts\Http\ResponseFactory;
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
     * Mock of a response factory.
     *
     * @var MockInterface|ResponseFactory
     */
    protected $responseFactory;
    /**
     * Mock of an adapter factory.
     *
     * @var MockInterface|AdapterFactory
     */
    protected $adapterFactory;
    /**
     * Mock of an error message resolver.
     *
     * @var MockInterface|ErrorMessageRegistry
     */
    protected $messageRegistry;
    /**
     * Mock of a response formatter.
     *
     * @var MockInterface|Formatter
     */
    protected $formatter;
    /**
     * Class being tested.
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
        $this->messageRegistry = mock(ErrorMessageRegistry::class);
        $this->responseBuilder = new ErrorResponseBuilder($this->responseFactory, $this->adapterFactory, $this->messageRegistry);
    }
}
