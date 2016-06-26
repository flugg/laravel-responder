<?php

namespace Mangopixel\Responder;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use League\Fractal\Manager;
use Mangopixel\Responder\Console\MakeTransformer;
use Mangopixel\Responder\Contracts\Manageable;
use Mangopixel\Responder\Contracts\Respondable;

/**
 * The Laravel Responder service provider, which is where the package is bootstrapped.
 *
 * @package Laravel Responder
 * @author  Alexander Tømmerås <flugged@gmail.com>
 * @license The MIT License
 */
class ResponderServiceProvider extends BaseServiceProvider
{
    /**
     * Keeps a short reference to the package configurations.
     *
     * @var array
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
        $this->publishes( [
            __DIR__ . '/../resources/config/responder.php' => config_path( 'responder.php' )
        ], 'config' );

        $this->commands( [
            MakeTransformer::class
        ] );
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom( __DIR__ . '/../resources/config/responder.php', 'responder' );
        $this->config = $this->app[ 'config' ]->get( 'responder' );

        $this->app->singleton( Respondable::class, function () {
            return new Responder();
        } );

        $this->app->singleton( Manageable::class, function () {
            return ( new Manager() )->setSerializer( new $this->config[ 'serializer' ] );
        } );
    }
}