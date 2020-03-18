<?php

namespace Flugg\Responder;

use Flugg\Responder\Contracts\AdapterFactory as AdapterFactoryContract;
use Flugg\Responder\Contracts\ErrorMessageRegistry as ErrorMessageRegistryContract;
use Flugg\Responder\Contracts\Http\ResponseFactory;
use Flugg\Responder\Contracts\Http\ResponseFormatter;
use Flugg\Responder\Contracts\Responder as ResponderContract;
use Flugg\Responder\ErrorMessageRegistry;
use Flugg\Responder\Exceptions\Handler;
use Flugg\Responder\Http\Builders\ErrorResponseBuilder;
use Flugg\Responder\Http\Builders\SuccessResponseBuilder;
use Flugg\Responder\Http\Factories\LaravelResponseFactory;
use Flugg\Responder\Http\Factories\LumenResponseFactory;
use Flugg\Responder\Testing\AssertErrorMacro;
use Flugg\Responder\Testing\AssertSuccessMacro;
use Flugg\Responder\Testing\AssertValidationErrorsMacro;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Application as Laravel;
use Illuminate\Foundation\Testing\TestResponse as LegacyTestResponse;
use Illuminate\Support\ServiceProvider;
use Illuminate\Testing\TestResponse;
use Laravel\Lumen\Application as Lumen;

/**
 * Service provider bootstrapping the Laravel Responder package.
 *
 * @package flugger/laravel-responder
 * @author Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class ResponderServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     * @throws BindingResolutionException
     */
    public function register(): void
    {
        $this->registerResponseFactory();
        $this->registerAdapterFactory();
        $this->registerErrorMessageRegistry();
        $this->registerResponseFormatter();
        $this->registerResponderService();
        $this->registerExceptionHandler();
        $this->registerTestingMacros();
    }

    /**
     * Register response factory binding with configured decorators.
     *
     * @return void
     * @throws BindingResolutionException
     */
    protected function registerResponseFactory(): void
    {
        $this->app->singleton(ResponseFactory::class, function () {
            $class = $this->app instanceof Lumen ? LumenResponseFactory::class : LaravelResponseFactory::class;
            $factory = $this->app->make($class);

            foreach ((config('responder.decorators') ?? []) as $decorator) {
                $factory = new $decorator($factory);
            }

            return $factory;
        });
    }

    /**
     * Register adapter factory binding.
     *
     * @return void
     * @throws BindingResolutionException
     */
    protected function registerAdapterFactory(): void
    {
        $this->app->singleton(AdapterFactoryContract::class, function () {
            return new AdapterFactory(config('responder.adapters') ?? []);
        });
    }

    /**
     * Register error message resolver binding with configured error messages.
     *
     * @return void
     * @throws BindingResolutionException
     */
    protected function registerErrorMessageRegistry(): void
    {
        $this->app->singleton(ErrorMessageRegistryContract::class, function () {
            return tap($this->app->make(ErrorMessageRegistry::class), function (ErrorMessageRegistry $messageRegistry) {
                $messageRegistry->register(config('responder.error_messages'));
            });
        });
    }

    /**
     * Register configured response formatter binding and extend response builders with formatter.
     *
     * @return void
     * @throws BindingResolutionException
     */
    protected function registerResponseFormatter(): void
    {
        $this->app->singleton(ResponseFormatter::class, function () {
            return is_null($class = config('responder.formatter')) ? null : $this->app->make($class);
        });

        foreach ([SuccessResponseBuilder::class, ErrorResponseBuilder::class] as $class) {
            $this->app->extend($class, function ($responseBuilder) {
                return $responseBuilder->formatter($this->app->make(ResponseFormatter::class));
            });
        }
    }

    /**
     * Register responder service binding.
     *
     * @return void
     * @throws BindingResolutionException
     */
    protected function registerResponderService(): void
    {
        $this->app->bind(ResponderContract::class, Responder::class);
    }

    /**
     * Register exception handler by decorating the bound handler.
     *
     * @return void
     * @throws BindingResolutionException
     */
    protected function registerExceptionHandler(): void
    {
        $this->app->extend(ExceptionHandler::class, function ($handler) {
            return new Handler($handler, $this->app->make(ResponderContract::class));
        });
    }

    /**
     * Register test response macros.
     *
     * @return void
     * @throws BindingResolutionException
     */
    protected function registerTestingMacros(): void
    {
        if ($testResponse = $this->getTestResponse()) {
            $testResponse::macro('assertSuccess', $this->app->make(AssertSuccessMacro::class)());
            $testResponse::macro('assertError', $this->app->make(AssertErrorMacro::class)());
            $testResponse::macro('assertValidationErrors', $this->app->make(AssertValidationErrorsMacro::class)());
        }
    }

    /**
     * Register test response macros.
     *
     * @return string|null
     */
    protected function getTestResponse(): ?string
    {
        if (class_exists(TestResponse::class)) {
            return TestResponse::class;
        } else if (class_exists(LegacyTestResponse::class)) {
            return LegacyTestResponse::class;
        }

        return null;
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot(): void
    {
        if ($this->app instanceof Laravel && $this->app->runningInConsole()) {
            $this->publishes([__DIR__ . '/../config/responder.php' => config_path('responder.php')], 'config');
        } elseif ($this->app instanceof Lumen) {
            $this->app->configure('responder');
        }

        $this->mergeConfigFrom(__DIR__ . '/../config/responder.php', 'responder');
    }
}
