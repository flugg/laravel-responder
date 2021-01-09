<?php

namespace Flugg\Responder\Tests\Integration;

use Flugg\Responder\Contracts\ErrorMessageRegistry as ErrorMessageRegistryContract;
use Flugg\Responder\Contracts\Http\Formatter;
use Flugg\Responder\Contracts\Http\ResponseFactory;
use Flugg\Responder\Contracts\Responder as ResponderContract;
use Flugg\Responder\ErrorMessageRegistry;
use Flugg\Responder\Exceptions\Handler;
use Flugg\Responder\Http\Builders\ErrorResponseBuilder;
use Flugg\Responder\Http\Builders\SuccessResponseBuilder;
use Flugg\Responder\Http\Decorators\PrettyPrintDecorator;
use Flugg\Responder\Http\Factories\LaravelResponseFactory;
use Flugg\Responder\Http\Formatters\JsonApiFormatter;
use Flugg\Responder\Http\Formatters\SimpleFormatter;
use Flugg\Responder\Responder;
use Flugg\Responder\Tests\IntegrationTestCase;
use Illuminate\Contracts\Debug\ExceptionHandler;

/**
 * Integration tests for testing the bootstrapping of the package.
 */
class BootstrapPackageTest extends IntegrationTestCase
{
    /**
     * Assert that the package registers binding for the [ErrorMessageRegistry] interface.
     */
    public function testErrorMessageRegistryBinding()
    {
        $result = $this->app->make(ErrorMessageRegistryContract::class);

        $this->assertInstanceOf(ErrorMessageRegistry::class, $result);
    }

    /**
     * Assert that the [ErrorMessageRegistry] binding is a singleton.
     */
    public function testErrorMessageRegistryIsSingleton()
    {
        $singleton = $this->app->make(ErrorMessageRegistryContract::class);
        $result = $this->app->make(ErrorMessageRegistryContract::class);

        $this->assertSame($singleton, $result);
    }

    /**
     * Assert that the package registers configured error messages in the [ErrorMessageRegistry] class.
     */
    public function testErrorMessageRegistrySetsConfiguredErrorMessages()
    {
        $result = $this->app->make(ErrorMessageRegistryContract::class);

        foreach (config('responder.error_messages') as $code => $message) {
            $this->assertSame($message, $result->resolve($code));
        }
    }

    /**
     * Assert that the package registers binding for the [Formatter] interface.
     */
    public function testResponseFormatterBinding()
    {
        $result = $this->app->make(Formatter::class);

        $this->assertInstanceOf(SimpleFormatter::class, $result);
    }

    /**
     * Assert that the [Formatter] binding is a singleton.
     */
    public function testResponseFormatterRegistryIsSingleton()
    {
        $singleton = $this->app->make(Formatter::class);
        $result = $this->app->make(Formatter::class);

        $this->assertSame($singleton, $result);
    }

    /**
     * Assert that the configured response formatter is resolved from the [Formatter] binding.
     */
    public function testResponseFormatterCanBeConfigured()
    {
        config()->set('responder.formatter', JsonApiFormatter::class);

        $result = $this->app->make(Formatter::class);

        $this->assertInstanceOf(JsonApiFormatter::class, $result);
    }

    /**
     * Assert that the [ResponseFactory] binding can be null.
     */
    public function testResponseFormatterCanBeNull()
    {
        config()->set('responder.formatter', null);

        $result = $this->app->make(Formatter::class);

        $this->assertNull($result);
    }

    /**
     * Assert that success response builders are extended to include configured formatter.
     */
    public function testSuccessResponseBuildersAreExtendedWithResponseFormatter()
    {
        $formatter = $this->spy(Formatter::class);
        $responseBuilder = $this->app->make(SuccessResponseBuilder::class)->make();

        $responseBuilder->respond();

        $formatter->shouldHaveReceived('success');
    }

    /**
     * Assert that success response builders are extended to include configured formatter.
     */
    public function testErrorResponseBuildersAreExtendedWithResponseFormatter()
    {
        $formatter = $this->spy(Formatter::class);
        $responseBuilder = $this->app->make(ErrorResponseBuilder::class)->make();

        $responseBuilder->respond();

        $formatter->shouldHaveReceived('error');
    }

    /**
     * Assert that the package registers binding for the [ResponseFactory] interface.
     */
    public function testResponseFactoryBinding()
    {
        $result = $this->app->make(ResponseFactory::class);

        $this->assertInstanceOf(LaravelResponseFactory::class, $result);
    }

    /**
     * Assert that the [ResponseFactory] binding is a singleton.
     */
    public function testResponseFactoryIsSingleton()
    {
        $singleton = $this->app->make(ResponseFactory::class);
        $result = $this->app->make(ResponseFactory::class);

        $this->assertSame($singleton, $result);
    }

    /**
     * Assert that configured response decorators are applied to the [ResponseFactory] binding.
     */
    public function testResponseFactoryCanBeDecorated()
    {
        config()->set('responder.decorators', [PrettyPrintDecorator::class]);

        $result = $this->app->make(ResponseFactory::class);

        $this->assertInstanceOf(PrettyPrintDecorator::class, $result);
    }

    /**
     * Assert that the package registers binding for the [Responder] interface.
     */
    public function testResponderServiceBinding()
    {
        $result = $this->app->make(ResponderContract::class);

        $this->assertInstanceOf(Responder::class, $result);
    }

    /**
     * Assert that the [Responder] binding is not a singleton.
     */
    public function testResponderServiceIsNotSingleton()
    {
        $instance = $this->app->make(ResponderContract::class);
        $result = $this->app->make(ResponderContract::class);

        $this->assertNotSame($instance, $result);
    }

    /**
     * Assert that the bound exception handler is decorated with the package handler.
     */
    public function testExceptionHandlerBinding()
    {
        $result = $this->app->make(ExceptionHandler::class);

        $this->assertInstanceOf(Handler::class, $result);
    }
}
