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
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use Orchestra\Testbench\TestCase as BaseTestCase;

/**
 * This is the base test case class and is where the testing environment bootstrapping
 * takes place. All other testing classes should extend this class.
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
     * Create a mock of a transformer just returning the unmodified data directly.
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
     * Create a mock of a transform builder.
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
     * Create a mock of a response factory.
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
     * Create a mock of an error response builder.
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
     * Create a mock of a success response builder.
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
     * Create a mock of a Fractal manager.
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
}