<?php

namespace Flugg\Responder;

use Flugg\Responder\Contracts\ErrorMessageRegistry as ErrorMessageRegistryContract;
use Flugg\Responder\Contracts\Http\Formatter;
use Flugg\Responder\Contracts\Http\ResponseFactory;
use Flugg\Responder\Contracts\Responder as ResponderContract;
use Flugg\Responder\Exceptions\Handler;
use Flugg\Responder\Http\Builders\ErrorResponseBuilder;
use Flugg\Responder\Http\Builders\SuccessResponseBuilder;
use Flugg\Responder\Http\Factories\LaravelResponseFactory;
use Flugg\Responder\Http\Factories\LumenResponseFactory;
use Flugg\Responder\Testing\AssertErrorMacro;
use Flugg\Responder\Testing\AssertSuccessMacro;
use Flugg\Responder\Testing\AssertValidationErrorsMacro;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Application as Laravel;
use Illuminate\Support\ServiceProvider;
use Illuminate\Testing\TestResponse;
use Laravel\Lumen\Application as Lumen;

/**
 * Service provider bootstrapping the Laravel Responder package.
 */
class ResponderServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @return void
     */
    public function register(): void
    {
        $this->registerErrorMessageRegistry();
        $this->registerResponseFactory();
        $this->registerResponseFormatter();
        $this->registerResponderService();
        $this->registerExceptionHandler();
        $this->registerTestingMacros();
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     * @codeCoverageIgnore
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

    /**
     * Register error message resolver binding with configured error messages.
     *
     * @return void
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
     * Register response factory binding with configured decorators.
     *
     * @return void
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
     * Register configured response formatter binding and extend response builders with formatter.
     *
     * @return void
     */
    protected function registerResponseFormatter(): void
    {
        $this->app->singleton(Formatter::class, function () {
            return ($class = config('responder.formatter')) ? $this->app->make($class) : null;
        });

        foreach ([SuccessResponseBuilder::class, ErrorResponseBuilder::class] as $class) {
            $this->app->extend($class, function ($responseBuilder) {
                return $responseBuilder->formatter($this->app->make(Formatter::class));
            });
        }
    }

    /**
     * Register responder service binding.
     *
     * @return void
     */
    protected function registerResponderService(): void
    {
        $this->app->bind(ResponderContract::class, Responder::class);
    }

    /**
     * Register exception handler by decorating the bound handler.
     *
     * @return void
     */
    protected function registerExceptionHandler(): void
    {
        $this->app->extend(ExceptionHandler::class, function ($handler) {
            return new Handler($handler, $this->app->config, $this->app->make(ResponderContract::class));
        });
    }

    /**
     * Register test response macros.
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @return void
     */
    protected function registerTestingMacros(): void
    {
        TestResponse::macro('assertSuccess', $this->app->make(AssertSuccessMacro::class)());
        TestResponse::macro('assertError', $this->app->make(AssertErrorMacro::class)());
        TestResponse::macro('assertValidationErrors', $this->app->make(AssertValidationErrorsMacro::class)());
    }
}
