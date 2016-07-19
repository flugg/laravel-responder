<?php

namespace Flugg\Responder;

use Flugg\Responder\Console\MakeTransformer;
use Flugg\Responder\Contracts\Manager as ManagerContract;
use Flugg\Responder\Contracts\Responder as ResponderContract;
use Flugg\Responder\Factories\ErrorResponseFactory;
use Flugg\Responder\Factories\SuccessResponseFactory;
use Illuminate\Foundation\Application as Laravel;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Laravel\Lumen\Application as Lumen;
use League\Fractal\Manager;

/**
 * The Laravel Responder service provider. This is where the package is bootstrapped.
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
        if ( $this->app instanceof Laravel && $this->app->runningInConsole() ) {
            $this->bootLaravelApplication();

        } elseif ( $this->app instanceof Lumen ) {
            $this->bootLumenApplication();
        }

        $this->mergeConfigFrom( __DIR__ . '/../resources/config/responder.php', 'responder' );

        $this->commands( [
            MakeTransformer::class
        ] );

        include __DIR__ . '/helpers.php';
    }


    /**
     * Bootstrap the Laravel application.
     *
     * @return void
     */
    protected function bootLaravelApplication()
    {
        $this->publishes( [
            __DIR__ . '/../resources/config/responder.php' => config_path( 'responder.php' )
        ], 'config' );

        $this->publishes( [
            __DIR__ . '/../resources/lang/en/errors.php' => resource_path( 'lang/en/errors.php' )
        ], 'lang' );
    }

    /**
     * Bootstrap the Lumen application.
     *
     * @return void
     */
    protected function bootLumenApplication()
    {
        $this->app->configure( 'responder' );
    }


    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $config = $this->app[ 'config' ];

        $this->app->singleton( 'responder.success', function () use ( $config ) {
            return new SuccessResponseFactory( $config->get( 'responder.status_code' ) );
        } );

        $this->app->singleton( 'responder.error', function () use ( $config ) {
            return new ErrorResponseFactory( $config->get( 'responder.status_code' ) );
        } );

        $this->app->singleton( 'responder', function ( $app ) {
            return ( new Responder( $app[ 'responder.success' ], $app[ 'responder.error' ] ) );
        } );

        $this->app->singleton( 'responder.manager', function () use ( $config ) {
            $serializer = $config->get( 'responder.serializer' );

            return ( new Manager() )->setSerializer( new $serializer );
        } );

        $this->app->alias( 'responder', ResponderContract::class );
        $this->app->alias( 'responder.manager', ManagerContract::class );
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [ 'responder', 'responder.manager' ];
    }
}