<?php

namespace Krenor\Http2Pusher\Providers;

use Illuminate\Support\ServiceProvider;
use Krenor\Http2Pusher\Factories\ResponseFactory;
use Illuminate\Contracts\View\Factory as ViewFactoryContract;
use Illuminate\Contracts\Routing\ResponseFactory as ResponseFactoryContract;

class Http2PusherServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->registerResponse();

        $this->registerBuilder();
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

    /**
     * Register the builder for HTTP2 pushes.
     *
     * @return void
     */
    private function registerBuilder()
    {
        $this->app->singleton(Builder::class, function ($app) {
            return new Builder($app['request']);
        });
    }
}
