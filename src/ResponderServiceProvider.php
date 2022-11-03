<?php

namespace Flugg\Responder;

use Flugg\Responder\Console\MakeTransformer;
use Flugg\Responder\Http\ErrorResponseBuilder;
use Flugg\Responder\Http\SuccessResponseBuilder;
use Flugg\Responder\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Application as Laravel;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Laravel\Lumen\Application as Lumen;
use League\Fractal\Manager;
use League\Fractal\Serializer\SerializerAbstract;

/**
 * The Laravel Responder service provider. This is where the package is bootstrapped.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class ResponderServiceProvider extends BaseServiceProvider
{
    /**
     * Keeps a quick reference to the Responder config.
     *
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

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

        $this->mergeConfigFrom(__DIR__ . '/../resources/config/responder.php', 'responder');
        $this->commands([
            MakeTransformer::class,
        ]);

        include __DIR__ . '/helpers.php';
    }

    /**
     * Bootstrap the Laravel application.
     *
     * @return void
     */
    protected function bootLaravelApplication()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__ . '/../resources/config/responder.php' => config_path('responder.php')], 'config');
            $this->publishes([__DIR__ . '/../resources/lang/en/errors.php' => base_path('resources/lang/en/errors.php')], 'lang');
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

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerSerializer();
        $this->registerManager();
        $this->registerResourceFactory();
        $this->registerTransformationFactory();
        $this->registerSuccessResponseBuilder();
        $this->registerErrorResponseBuilder();
        $this->registerResponder();
        $this->registerAliases();

        CursorPaginator::cursorResolver(function ($cursorName) {
            return $this->app['request']->input($cursorName);
        });

        Builder::macro('paginateByCursor', function ($limit = 15, $columns = ['*'], $whereColumn = 'id', $constraint = '>', $callback = null) {
            if ($cursor = CursorPaginator::resolveCursor()) {
                $this->where($whereColumn, $constraint, $cursor);
            }

            $results = $this->take($limit)->get($columns);
            if ($results->count() < $limit) {
                $nextCursor = null;
            } elseif (is_callable($callback)) {
                $nextCursor = $callback($results->last());
            } else {
                $nextCursor = $results->count() < $limit ? null : $results->last()->{array_last(explode('.', $whereColumn))};
            }

            return new CursorPaginator($results, $cursor, $nextCursor);
        });;

        Relation::macro('paginateByCursor', function ($limit = 15, $columns = ['*'], $whereColumn = 'id', $constraint = '>', $callback = null) {
            if ($this instanceof BelongsToMany || $this instanceof HasManyThrough) {
                $this->getQuery()->addSelect($this->shouldSelect($columns));
            }

            if ($this instanceof BelongsToMany) {
                return tap($this->getQuery()->paginateByCursor($limit, $columns, "{$this->getRelated()->getTable()}.{$whereColumn}", $constraint), function ($paginator) {
                    $this->hydratePivotRelation($paginator->items());
                });
            }

            return $this->getQuery()->paginateByCursor($limit, $columns, $whereColumn, $constraint, $callback);
        });
    }

    /**
     * Register the active serializer to the servide provider.
     *
     * @return void
     */
    protected function registerSerializer()
    {
        $this->app->bind(SerializerAbstract::class, function ($app) {
            $serializer = $app->config->get('responder.serializer');

            return $app->make($serializer);
        });
    }

    /**
     * Register the Fractal manager to the servide provider.
     *
     * @return void
     */
    protected function registerManager()
    {
        $this->app->bind(Manager::class, function ($app) {
            return (new Manager)->setSerializer($app[SerializerAbstract::class]);
        });
    }

    /**
     * Register the resource factory to the servide provider.
     *
     * @return void
     */
    protected function registerResourceFactory()
    {
        $this->app->bind(ResourceFactory::class, function () {
            return new ResourceFactory;
        });
    }

    /**
     * Register the transformation factory to the servide provider.
     *
     * @return void
     */
    protected function registerTransformationFactory()
    {
        $this->app->bind(TransformationFactory::class, function ($app) {
            return new TransformationFactory($app[Manager::class], $app[ResourceFactory::class]);
        });
    }

    /**
     * Register the success response builder to the servide provider.
     *
     * @return void
     */
    protected function registerSuccessResponseBuilder()
    {
        $this->app->bind(SuccessResponseBuilder::class, function ($app) {
            $builder = new SuccessResponseBuilder(response(), $app[TransformationFactory::class]);

            if ($parameter = $app->config->get('responder.load_relations_from_parameter')) {
                $builder->with($this->app[Request::class]->input($parameter, []));
            }

            if ($app->config->get('responder.include_status_code')) {
                $builder->outputStatusCode();
            }

            return $builder;
        });
    }

    /**
     * Register the error response builder to the servide provider.
     *
     * @return void
     */
    protected function registerErrorResponseBuilder()
    {
        $this->app->bind(ErrorResponseBuilder::class, function ($app) {
            $builder = new ErrorResponseBuilder(response(), $app['translator']);

            if ($app->config->get('responder.include_status_code')) {
                $builder->outputStatusCode();
            }

            return $builder;
        });
    }

    /**
     * Register the error response builder to the servide provider.
     *
     * @return void
     */
    protected function registerResponder()
    {
        $this->app->bind(Responder::class, function ($app) {
            return new Responder($app['config'], $app[ErrorResponseBuilder::class], $app[SuccessResponseBuilder::class]);
        });
    }

    /**
     * Set aliases for the provided services.
     *
     * @return void
     */
    protected function registerAliases()
    {
        $this->app->alias(Responder::class, 'responder');
        $this->app->alias(ResourceFactory::class, 'responder.resource');
        $this->app->alias(TransformationFactory::class, 'responder.transformation');
        $this->app->alias(SuccessResponseBuilder::class, 'responder.success');
        $this->app->alias(ErrorResponseBuilder::class, 'responder.error');
        $this->app->alias(Manager::class, 'responder.manager');
        $this->app->alias(SerializerAbstract::class, 'responder.serializer');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['responder', 'responder.success', 'responder.error', 'responder.manager', 'responder.serializer'];
    }
}