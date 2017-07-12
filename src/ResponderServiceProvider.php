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
        $this->app->bind(ErrorSerializerContract::class, function () {
            return new ErrorSerializer;
        });

        $this->app->bind(ErrorFactoryContract::class, function ($app) {
            return new ErrorFactory($app[ErrorMessageResolver::class], $app[ErrorSerializer::class]);
        });

        $this->app->bind(TransformFactory::class, function () {
            return new FractalTransformFactory(new Manager);
        });

        $this->app->bind(TransformerContract::class, function ($app) {
            return new Transformer($app[TransformBuilder::class]);
        });

        $this->app->bind(ResponseFactoryContract::class, function ($app) {
            return new LaravelResponseFactory($app[ResponseFactory::class]);
        });

        $this->app->bind(ResponderContract::class, function ($app) {
            return new Responder($app[SuccessResponseBuilder::class], $app[ErrorResponseBuilder::class]);
        });

        $this->app->bind(PaginatorFactory::class, function ($app) {
            return new PaginatorFactory($app[Request::class]->query());
        });

        $this->app->bind(CursorFactory::class, function ($app) {
            return new CursorFactory($app[Request::class]->query());
        });
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}