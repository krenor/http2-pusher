<?php

namespace Krenor\Http2Pusher\Providers;

use Krenor\Http2Pusher\Builder;
use Illuminate\Support\ServiceProvider;
use Krenor\Http2Pusher\Factories\ResponseFactory;
use Illuminate\Contracts\View\Factory as ViewFactoryContract;
use Illuminate\Contracts\Routing\ResponseFactory as ResponseFactoryContract;

class Http2PusherServiceProvider extends ServiceProvider
{
    /**
     * Path to the default configuration file.
     *
     * @var string
     */
    private $config = __DIR__ . '/../../config/http2-pusher.php';

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            $this->config => config_path('http2-pusher.php'),
        ], 'config');
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom($this->config, 'http2-pusher');

        $this->registerBuilder();

        $this->registerResponse();
    }

    /**
     * Register the builder for HTTP2 pushes.
     *
     * @return void
     */
    private function registerBuilder()
    {
        $this->app->singleton(Builder::class, function ($app) {
            return new Builder(
                $app['request'],
                $app['config']['http2-pusher']
            );
        });
    }

    /**
     * Override the response factory provided by Laravel.
     *
     * @return void
     */
    private function registerResponse()
    {
        $this->app->singleton(ResponseFactoryContract::class, function ($app) {
            return new ResponseFactory($app[ViewFactoryContract::class], $app['redirect']);
        });
    }
}
