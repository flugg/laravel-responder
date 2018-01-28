<?php

namespace Flugg\Responder;

use Flugg\Responder\Console\MakeTransformer;
use Flugg\Responder\Contracts\ErrorFactory as ErrorFactoryContract;
use Flugg\Responder\Contracts\ErrorMessageResolver as ErrorMessageResolverContract;
use Flugg\Responder\Contracts\ErrorSerializer as ErrorSerializerContract;
use Flugg\Responder\Contracts\Pagination\PaginatorFactory as PaginatorFactoryContract;
use Flugg\Responder\Contracts\Resources\ResourceFactory as ResourceFactoryContract;
use Flugg\Responder\Contracts\Resources\ResourceKeyResolver as ResourceKeyResolverContract;
use Flugg\Responder\Contracts\Responder as ResponderContract;
use Flugg\Responder\Contracts\ResponseFactory;
use Flugg\Responder\Contracts\ResponseFactory as ResponseFactoryContract;
use Flugg\Responder\Contracts\SimpleTransformer as SimpleTransformerContract;
use Flugg\Responder\Contracts\Transformers\TransformerResolver as TransformerResolverContract;
use Flugg\Responder\Contracts\TransformFactory as TransformFactoryContract;
use Flugg\Responder\Http\Responses\ErrorResponseBuilder;
use Flugg\Responder\Http\Responses\Factories\LaravelResponseFactory;
use Flugg\Responder\Http\Responses\Factories\LumenResponseFactory;
use Flugg\Responder\Pagination\PaginatorFactory;
use Flugg\Responder\Resources\ResourceFactory;
use Flugg\Responder\Resources\ResourceKeyResolver;
use Flugg\Responder\Transformers\Transformer as BaseTransformer;
use Flugg\Responder\Transformers\TransformerResolver;
use Illuminate\Contracts\Container\Container;
use Illuminate\Foundation\Application as Laravel;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Illuminate\Translation\Translator;
use Laravel\Lumen\Application as Lumen;
use League\Fractal\Manager;
use League\Fractal\Serializer\SerializerAbstract;

/**
 * A service provider class responsible for bootstrapping the parts of the Laravel package.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class ResponderServiceProvider extends BaseServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app instanceof Laravel) {
            $this->registerLaravelBindings();
        } elseif ($this->app instanceof Lumen) {
            $this->registerLumenBindings();
        }

        $this->registerSerializerBindings();
        $this->registerErrorBindings();
        $this->registerFractalBindings();
        $this->registerTransformerBindings();
        $this->registerResourceBindings();
        $this->registerPaginationBindings();
        $this->registerTransformationBindings();
        $this->registerServiceBindings();
    }

    /**
     * Register Laravel bindings.
     *
     * @return void
     */
    protected function registerLaravelBindings()
    {
        $this->app->singleton(ResponseFactoryContract::class, function ($app) {
            return $this->decorateResponseFactory($app->make(LaravelResponseFactory::class));
        });
    }

    /**
     * Register Lumen bindings.
     *
     * @return void
     */
    protected function registerLumenBindings()
    {
        $this->app->singleton(ResponseFactoryContract::class, function ($app) {
            return $this->decorateResponseFactory($app->make(LumenResponseFactory::class));
        });

        $this->app->bind(Translator::class, function ($app) {
            return $app['translator'];
        });
    }

    /**
     * Decorate response factories.
     *
     * @param  \Flugg\Responder\Contracts\ResponseFactory $factory
     * @return \Flugg\Responder\Contracts\ResponseFactory
     */
    protected function decorateResponseFactory(ResponseFactoryContract $factory): ResponseFactory
    {
        foreach ($this->app->config['responder.decorators'] as $decorator) {
            $factory = new $decorator($factory);
        };

        return $factory;
    }

    /**
     * Register serializer bindings.
     *
     * @return void
     */
    protected function registerSerializerBindings()
    {
        $this->app->bind(ErrorSerializerContract::class, function ($app) {
            return $app->make($app->config['responder.serializers.error']);
        });

        $this->app->bind(SerializerAbstract::class, function ($app) {
            return $app->make($app->config['responder.serializers.success']);
        });
    }

    /**
     * Register error bindings.
     *
     * @return void
     */
    protected function registerErrorBindings()
    {
        $this->app->singleton(ErrorMessageResolverContract::class, function ($app) {
            return $app->make(ErrorMessageResolver::class);
        });

        $this->app->singleton(ErrorFactoryContract::class, function ($app) {
            return $app->make(ErrorFactory::class);
        });

        $this->app->bind(ErrorResponseBuilder::class, function ($app) {
            return (new ErrorResponseBuilder($app->make(ResponseFactoryContract::class), $app->make(ErrorFactoryContract::class)))->serializer($app->make(ErrorSerializerContract::class));
        });
    }

    /**
     * Register Fractal bindings.
     *
     * @return void
     */
    protected function registerFractalBindings()
    {
        $this->app->bind(Manager::class, function ($app) {
            return (new Manager)->setRecursionLimit($app->config['responder.recursion_limit']);
        });
    }

    /**
     * Register transformer bindings.
     *
     * @return void
     */
    protected function registerTransformerBindings()
    {
        $this->app->singleton(TransformerResolverContract::class, function ($app) {
            return new TransformerResolver($app, $app->config['responder.fallback_transformer']);
        });

        BaseTransformer::containerResolver(function () {
            return $this->app->make(Container::class);
        });
    }

    /**
     * Register pagination bindings.
     *
     * @return void
     */
    protected function registerResourceBindings()
    {
        $this->app->singleton(ResourceKeyResolverContract::class, function ($app) {
            return $app->make(ResourceKeyResolver::class);
        });

        $this->app->singleton(ResourceFactoryContract::class, function ($app) {
            return $app->make(ResourceFactory::class);
        });
    }

    /**
     * Register pagination bindings.
     *
     * @return void
     */
    protected function registerPaginationBindings()
    {
        $this->app->bind(PaginatorFactoryContract::class, function ($app) {
            return new PaginatorFactory($app->make(Request::class)->query());
        });
    }

    /**
     * Register transformation bindings.
     *
     * @return void
     */
    protected function registerTransformationBindings()
    {
        $this->app->bind(TransformFactoryContract::class, function ($app) {
            return $app->make(FractalTransformFactory::class);
        });

        $this->app->bind(TransformBuilder::class, function ($app) {
            $request = $this->app->make(Request::class);
            $relations = $request->input($this->app->config['responder.load_relations_parameter'], []);
            $fieldsets = $request->input($app->config['responder.filter_fields_parameter'], []);

            return (new TransformBuilder($app->make(ResourceFactoryContract::class), $app->make(TransformFactoryContract::class), $app->make(PaginatorFactoryContract::class)))->serializer($app->make(SerializerAbstract::class))
                ->with(is_string($relations) ? explode(',', $relations) : $relations)
                ->only($fieldsets);
        });
    }

    /**
     * Register service bindings.
     *
     * @return void
     */
    protected function registerServiceBindings()
    {
        $this->app->bind(ResponderContract::class, function ($app) {
            return $app->make(Responder::class);
        });
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app instanceof Laravel) {
            $this->bootLaravelApplication();
        } elseif ($this->app instanceof Lumen) {
            $this->bootLumenApplication();
        }

        $this->mergeConfigFrom(__DIR__ . '/../config/responder.php', 'responder');
        $this->commands(MakeTransformer::class);
    }

    /**
     * Bootstrap the Laravel application.
     *
     * @return void
     */
    protected function bootLaravelApplication()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/responder.php' => config_path('responder.php'),
            ], 'config');
            $this->publishes([
                __DIR__ . '/../resources/lang/en/errors.php' => base_path('resources/lang/en/errors.php'),
            ], 'lang');
        }
    }

    /**
     * Bootstrap the Lumen application.
     *
     * @return void
     */
    protected function bootLumenApplication()
    {
        $this->app->configure('responder');
    }
}