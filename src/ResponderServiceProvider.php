<?php

namespace Flugg\Responder;

use Flugg\Responder\Contracts\AdapterFactory as AdapterFactoryContract;
use Flugg\Responder\Contracts\Http\ErrorMessageResolver as ErrorMessageResolverContract;
use Flugg\Responder\Contracts\Http\ErrorResponseBuilder as ErrorResponseBuilderContract;
use Flugg\Responder\Contracts\Http\ResponseFactory;
use Flugg\Responder\Contracts\Http\ResponseFormatter;
use Flugg\Responder\Contracts\Http\SuccessResponseBuilder as SuccessResponseBuilderContract;
use Flugg\Responder\Contracts\Responder as ResponderContract;
use Flugg\Responder\Http\Builders\ErrorResponseBuilder;
use Flugg\Responder\Http\Builders\SuccessResponseBuilder;
use Flugg\Responder\Http\ErrorMessageResolver;
use Flugg\Responder\Http\Factories\LaravelResponseFactory;
use Flugg\Responder\Http\Factories\LumenResponseFactory;
use Flugg\Responder\Testing\AssertErrorMacro;
use Flugg\Responder\Testing\AssertSuccessMacro;
use Flugg\Responder\Testing\AssertValidationErrorsMacro;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Application as Laravel;
use Illuminate\Testing\TestResponse;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application as Lumen;

/**
 * A service provider class responsible for bootstrapping the package.
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
        $this->registerResponseFactory($this->app instanceof Lumen ? LumenResponseFactory::class : LaravelResponseFactory::class);
        $this->registerAdapterFactory();
        $this->registerErrorMessageResolver();
        $this->registerResponseFormatter();
        $this->registerResponseBuilders();
        $this->registerResponderService();
        $this->registerTestingMacros();
    }

    /**
     * Register response factory binding with configured decorators.
     *
     * @param string $class
     * @return void
     */
    protected function registerResponseFactory(string $class): void
    {
        $this->app->singleton(ResponseFactory::class, function () use ($class) {
            $factory = $this->app->make($class);

            foreach (config('responder.decorators') as $decorator) {
                $factory = new $decorator($factory);
            }

            return $factory;
        });
    }

    /**
     * Register adapter factory binding.
     *
     * @return void
     */
    protected function registerAdapterFactory(): void
    {
        $this->app->singleton(AdapterFactoryContract::class, function () {
            return tap($this->app->make(AdapterFactory::class), function ($factory) {
                $factory::$adapters = config('responder.adapters');
            });
        });
    }

    /**
     * Register error message resolver binding with configured error messages.
     *
     * @return void
     */
    protected function registerErrorMessageResolver(): void
    {
        $this->app->singleton(ErrorMessageResolverContract::class, function () {
            return tap($this->app->make(ErrorMessageResolver::class), function (ErrorMessageResolverContract $messageResolver) {
                $messageResolver->register(config('responder.error_messages'));
            });
        });
    }

    /**
     * Register configured response formatter binding.
     *
     * @return void
     */
    protected function registerResponseFormatter(): void
    {
        $this->app->singleton(ResponseFormatter::class, function () {
            return $this->app->make(config('responder.formatter'));
        });
    }

    /**
     * Register response builder bindings with default formatter set.
     *
     * @return void
     */
    protected function registerResponseBuilders(): void
    {
        $this->app->bind(SuccessResponseBuilderContract::class, function () {
            return $this->app->make(SuccessResponseBuilder::class)->formatter($this->app->make(ResponseFormatter::class));
        });

        $this->app->bind(ErrorResponseBuilderContract::class, function () {
            return $this->app->make(ErrorResponseBuilder::class)->formatter($this->app->make(ResponseFormatter::class));
        });
    }

    /**
     * Register responder service binding.
     *
     * @return void
     */
    protected function registerResponderService(): void
    {
        $this->app->bind(ResponderContract::class, function () {
            return $this->app->make(Responder::class);
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
        TestResponse::macro('assertSuccess', $this->app->make(AssertSuccessMacro::class)());
        TestResponse::macro('assertError', $this->app->make(AssertErrorMacro::class)());
        TestResponse::macro('assertValidationErrors', $this->app->make(AssertValidationErrorsMacro::class)());
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot(): void
    {
        if ($this->app instanceof Laravel) {
            if ($this->app->runningInConsole()) {
                $this->publishes([__DIR__ . '/../config/responder.php' => config_path('responder.php')], 'config');
            }
        } elseif ($this->app instanceof Lumen) {
            $this->app->configure('responder');
        }

        $this->mergeConfigFrom(__DIR__ . '/../config/responder.php', 'responder');
    }
}
