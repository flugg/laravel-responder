<?php

namespace Flugg\Responder;

use Flugg\Responder\Contracts\ErrorFactory as ErrorFactoryContract;
use Flugg\Responder\Contracts\ErrorSerializer as ErrorSerializerContract;
use Flugg\Responder\Contracts\Responder as ResponderContract;
use Flugg\Responder\Contracts\ResponseFactory as ResponseFactoryContract;
use Flugg\Responder\Contracts\Transformer as TransformerContract;
use Flugg\Responder\Contracts\TransformFactory;
use Flugg\Responder\Http\Responses\ErrorResponseBuilder;
use Flugg\Responder\Http\Responses\Factories\LaravelResponseFactory;
use Flugg\Responder\Http\Responses\SuccessResponseBuilder;
use Flugg\Responder\Pagination\CursorFactory;
use Flugg\Responder\Pagination\PaginatorFactory;
use Flugg\Responder\Serializers\ErrorSerializer;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use League\Fractal\Manager;

/**
 * A service provider class responsible for bootstrapping the parts of the Laravel package.
 *
 * @package flugger/laravel-responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class LaravelServiceProvider extends BaseServiceProvider
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
        $this->app->bind(ResponseFactoryContract::class, function ($app) {
            $factory = new LaravelResponseFactory($app[ResponseFactory::class]);

            foreach ($this->app->config['responder.decorators'] as $decorator) {
                $factory = new $decorator($factory);
            };

            return $factory;
        });
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../resources/config/responder.php' => config_path('responder.php')
        ], 'config');
        $this->publishes([
            __DIR__ . '/../resources/lang/en/errors.php' => base_path('resources/lang/en/errors.php')
        ], 'lang');
    }
}