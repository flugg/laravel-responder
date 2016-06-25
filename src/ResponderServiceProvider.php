<?php

namespace Mangopixel\Adjuster;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use League\Fractal\Manager;

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
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom( __DIR__ . '/../resources/config/responder.php', 'responder' );

        $this->app->singleton( 'responder.fractal', function () {
            return new Manager();
        } );
    }
}