<?php

namespace Krenor\Http2Pusher\Provider;

use Illuminate\Support\ServiceProvider;
use Krenor\Http2Pusher\Factory\ResponseFactory;
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
        $this->app->singleton(ResponseFactoryContract::class, function ($app) {
            return new ResponseFactory($app[ViewFactoryContract::class], $app['redirect']);
        });
    }
}
