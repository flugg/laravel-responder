<?php

namespace Flugg\Responder\Tests\Integration;

use Flugg\Responder\AdapterFactory;
use Flugg\Responder\Contracts\AdapterFactory as AdapterFactoryContract;
use Flugg\Responder\Contracts\ErrorMessageRegistry as ErrorMessageRegistryContract;
use Flugg\Responder\Contracts\Http\ResponseFactory;
use Flugg\Responder\Contracts\Http\ResponseFormatter;
use Flugg\Responder\Contracts\Responder as ResponderContract;
use Flugg\Responder\ErrorMessageRegistry;
use Flugg\Responder\Http\Builders\ErrorResponseBuilder;
use Flugg\Responder\Http\Builders\SuccessResponseBuilder;
use Flugg\Responder\Http\Decorators\PrettyPrintDecorator;
use Flugg\Responder\Http\Factories\LaravelResponseFactory;
use Flugg\Responder\Http\Formatters\SimpleFormatter;
use Flugg\Responder\Pagination\IlluminatePaginatorAdapter;
use Flugg\Responder\Responder;
use Flugg\Responder\Tests\IntegrationTestCase;
use Flugg\Responder\Validation\IlluminateValidatorAdapter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Validation\Validator;

/**
 * Integration tests for testing the bootstrapping of the package.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class BootstrapPackageTest extends IntegrationTestCase
{
    /**
     * Assert that...
     */
    public function testResponseFactoryBinding()
    {
        $result = $this->app->make(ResponseFactory::class);

        $this->assertInstanceOf(LaravelResponseFactory::class, $result);
    }

    /**
     * Assert that...
     */
    public function testResponseFactoryDecorators()
    {
        config()->set('responder.decorators', [
            PrettyPrintDecorator::class,
        ]);

        $result = $this->app->make(ResponseFactory::class);

        $this->assertInstanceOf(PrettyPrintDecorator::class, $result);
    }

    /**
     * Assert that...
     */
    public function testResponseFactoryIsSingleton()
    {
        $singleton = $this->app->make(ResponseFactory::class);
        $result = $this->app->make(ResponseFactory::class);

        $this->assertSame($singleton, $result);
    }

    /**
     * Assert that...
     */
    public function testAdapterFactoryBinding()
    {
        $result = $this->app->make(AdapterFactoryContract::class);

        $this->assertInstanceOf(AdapterFactory::class, $result);
    }

    /**
     * Assert that...
     */
    public function testAdapterFactoryIsSingleton()
    {
        $singleton = $this->app->make(AdapterFactoryContract::class);
        $result = $this->app->make(AdapterFactoryContract::class);

        $this->assertSame($singleton, $result);
    }

    /**
     * Assert that...
     */
    public function testAdapterFactorySetsConfiguredAdapters()
    {
        $adapterFactory = $this->app->make(AdapterFactoryContract::class);
        $paginator = $adapterFactory->makePaginator(mock(LengthAwarePaginator::class));
        $validator = $adapterFactory->makeValidator(mock(Validator::class));

        $this->assertInstanceOf(IlluminatePaginatorAdapter::class, $paginator);
        $this->assertInstanceOf(IlluminateValidatorAdapter::class, $validator);
    }

    /**
     * Assert that...
     */
    public function testErrorMessageRegistryBinding()
    {
        $result = $this->app->make(ErrorMessageRegistryContract::class);

        $this->assertInstanceOf(ErrorMessageRegistry::class, $result);
    }

    /**
     * Assert that...
     */
    public function testErrorMessageRegistryIsSingleton()
    {
        $singleton = $this->app->make(ErrorMessageRegistryContract::class);
        $result = $this->app->make(ErrorMessageRegistryContract::class);

        $this->assertSame($singleton, $result);
    }

    /**
     * Assert that...
     */
    public function testErrorMessageRegistrySetsConfiguredErrorMessages()
    {
        $result = $this->app->make(ErrorMessageRegistryContract::class);

        foreach (config('responder.error_messages') as $code => $message) {
            $this->assertSame($message, $result->resolve($code));
        }
    }

    /**
     * Assert that...
     */
    public function testResponseFormatterBinding()
    {
        $result = $this->app->make(ResponseFormatter::class);

        $this->assertInstanceOf(SimpleFormatter::class, $result);
    }

    /**
     * Assert that...
     */
    public function testResponseFormatterCanBeNull()
    {
        config()->set('responder.formatter', null);

        $result = $this->app->make(ResponseFormatter::class);

        $this->assertNull($result);
    }

    /**
     * Assert that...
     */
    public function testResponseBuildersAreConfiguredWithResponseFormatter()
    {
        return $successResponseBuilder = mock(SuccessResponseBuilder::class);
        $errorResponseBuilder = mock(SuccessResponseBuilder::class);

        $this->app->bind(SuccessResponseBuilder::class, function () {
            return $successResponseBuilder = mock(SuccessResponseBuilder::class);
        });

        $this->app->bind(SuccessResponseBuilder::class, function () {
            return $errorResponseBuilder = mock(SuccessResponseBuilder::class);
        });

        $this->app->instance(ErrorResponseBuilder::class, $errorResponseBuilder = mock(ErrorResponseBuilder::class));

        $result = $this->app->make(SuccessResponseBuilder::class);

        $this->assertNull(null);
    }

    /**
     * Assert that...
     */
    public function testResponderServiceBinding()
    {
        $result = $this->app->make(ResponderContract::class);

        $this->assertInstanceOf(Responder::class, $result);
    }

    /**
     * Assert that...
     */
    public function testResponderServiceIsNotSingleton()
    {
        $instance = $this->app->make(ResponderContract::class);
        $result = $this->app->make(ResponderContract::class);

        $this->assertNotSame($instance, $result);
    }
}
