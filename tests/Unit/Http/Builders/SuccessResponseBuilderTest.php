<?php

namespace Flugg\Responder\Tests\Unit\Http\Builders;

use Flugg\Responder\Contracts\AdapterFactory;
use Flugg\Responder\Contracts\Http\ResponseFactory;
use Flugg\Responder\Contracts\Http\ResponseFormatter;
use Flugg\Responder\Http\Builders\SuccessResponseBuilder;
use Flugg\Responder\Tests\UnitTestCase;
use Mockery\MockInterface;

/**
 * Unit tests for the [Flugg\Responder\Http\Builders\SuccessResponseBuilder] class.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class SuccessResponseBuilderTest extends UnitTestCase
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
     * Mock of a response formatter.
     *
     * @var MockInterface|ResponseFormatter
     */
    protected $formatter;

    /**
     * Class being tested.
     *
     * @var SuccessResponseBuilder
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
        $this->responseBuilder = new SuccessResponseBuilder($this->responseFactory, $this->adapterFactory);
    }
}
