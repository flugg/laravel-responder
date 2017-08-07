<?php

namespace Flugg\Responder\Tests;

use Flugg\Responder\Contracts\ResponseFactory;
use Flugg\Responder\Http\Responses\ErrorResponseBuilder;
use Flugg\Responder\Http\Responses\SuccessResponseBuilder;
use Flugg\Responder\Resources\ResourceBuilder;
use Flugg\Responder\ResponderServiceProvider;
use Flugg\Responder\TransformBuilder;
use Flugg\Responder\Transformers\Transformer;
use Illuminate\Http\JsonResponse;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use Orchestra\Testbench\TestCase as BaseTestCase;

/**
 * The base test case class, responsible for bootstrapping the testing environment.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
abstract class TestCase extends BaseTestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);
    }

    /**
     * Get package service providers.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            ResponderServiceProvider::class,
        ];
    }

    /**
     * Create a mock of a [Transformer] returning the data directly.
     *
     * @return \Mockery\MockInterface
     */
    protected function mockTransformer(): MockInterface
    {
        $transformer = Mockery::mock(Transformer::class);

        $transformer->shouldReceive('transform')->andReturnUsing(function ($data) {
            return $data;
        });

        return $transformer;
    }

    /**
     * Create a mock of a [TransformBuilder].
     *
     * @return \Mockery\MockInterface
     */
    protected function mockTransformBuilder(): MockInterface
    {
        $transformBuilder = Mockery::mock(TransformBuilder::class);

        $transformBuilder->shouldReceive('resource')->andReturnSelf();
        $transformBuilder->shouldReceive('meta')->andReturnSelf();
        $transformBuilder->shouldReceive('with')->andReturnSelf();
        $transformBuilder->shouldReceive('without')->andReturnSelf();
        $transformBuilder->shouldReceive('serializer')->andReturnSelf();

        return $transformBuilder;
    }

    /**
     * Create a mock of a [ResponseFactory]].
     *
     * @return \Mockery\MockInterface
     */
    protected function mockResponseFactory(): MockInterface
    {
        $responseFactory = Mockery::mock(ResponseFactory::class);

        $responseFactory->shouldReceive('make')->andReturnUsing(function ($data, $status, $headers) {
            return new JsonResponse($data, $status, $headers);
        });

        return $responseFactory;
    }

    /**
     * Create a mock of an [ErrorResponseBuilder].
     *
     * @return \Mockery\MockInterface
     */
    protected function mockErrorResponseBuilder(): MockInterface
    {
        $responseBuilder = Mockery::mock(ErrorResponseBuilder::class);

        $responseBuilder->shouldReceive('error')->andReturnSelf();
        $responseBuilder->shouldReceive('data')->andReturnSelf();

        return $responseBuilder;
    }

    /**
     * Create a mock of a [SuccessResponseBuilder].
     *
     * @return \Mockery\MockInterface
     */
    protected function mockSuccessResponseBuilder(): MockInterface
    {
        $responseBuilder = Mockery::mock(SuccessResponseBuilder::class);

        $responseBuilder->shouldReceive('transform')->andReturnSelf();
        $responseBuilder->shouldReceive('meta')->andReturnSelf();

        return $responseBuilder;
    }

    /**
     * Create a mock of a Fractal [Manager].
     *
     * @return \Mockery\MockInterface
     */
    protected function mockFractalManager(): MockInterface
    {
        $responseBuilder = Mockery::mock(Manager::class);

        $responseBuilder->shouldReceive('setSerializer')->andReturnSelf()->byDefault();
        $responseBuilder->shouldReceive('parseIncludes')->andReturnSelf()->byDefault();
        $responseBuilder->shouldReceive('parseExcludes')->andReturnSelf()->byDefault();
        $responseBuilder->shouldReceive('parseFieldsets')->andReturnSelf()->byDefault();

        return $responseBuilder;
    }

    /**
     * Create a mock of a [ResourceInterface].
     *
     * @param  string|null $className
     * @return \Mockery\MockInterface
     */
    protected function mockResource(string $className = null): MockInterface
    {
        $resource = Mockery::mock($className ?: Collection::class);

        $resource->shouldReceive('getData')->andReturnNull()->byDefault();
        $resource->shouldReceive('getTransformer')->andReturnNull()->byDefault();
        $resource->shouldReceive('setMeta')->andReturnSelf()->byDefault();
        $resource->shouldReceive('setCursor')->andReturnSelf()->byDefault();
        $resource->shouldReceive('setPaginator')->andReturnSelf()->byDefault();

        return $resource;
    }
}