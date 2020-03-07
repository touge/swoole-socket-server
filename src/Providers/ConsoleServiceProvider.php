<?php

namespace Touge\SwooleSocketServer\Providers;

use Illuminate\Support\ServiceProvider;
use Touge\SwooleSocketServer\Console\SwooleServerCommand;

class ConsoleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the provided services.
     */
    public function boot()
    {
        //
    }

    /**
     * Register the provided services.
     */
    public function register()
    {
        $this->registerSwooleServerCommand();
    }

    /**
     * Register the module:list command.
     */
    protected function registerSwooleServerCommand()
    {
        $this->app->singleton('command.swoole.server', function ($app) {
            return new SwooleServerCommand();
        });

        $this->commands('command.swoole.server');
    }
}
