<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 2020-03-06
 * Time: 16:44
 */

namespace Touge\SwooleSocketServer\Providers;


use Illuminate\Support\ServiceProvider;
use Touge\SwooleSocketServer\Services\WebSocketServices;

class WebSocketServiceProvider extends ServiceProvider
{
    /**
     * Register the provided services.
     */
    public function register()
    {
        $this->app->singleton('websocket.server', function ($app) {
            return new WebSocketServices();
        });
    }
}