<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 2019-12-18
 * Time: 16:39
 */

namespace Touge\SwooleSocketServer;


use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Touge\SwooleSocketServer\Providers\ConsoleServiceProvider;
use Touge\SwooleSocketServer\Providers\WebSocketServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    protected $namespace = 'Touge\SwooleSocketServer\Controllers';
    protected $config_file= 'touge-swoole-server.php';


    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        if( !file_exists(config_path($this->config_file))){
            $this->loadConfig();
        }

//        $this->loadTranslationsFrom(__DIR__.'/../resource/lang', 'JwtAuth');
        $this->mapApiRoutes();

        /**
         * 发布资源内容
         */
        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__.'/../config' => config_path()], 'touge-swoole-server-config');
//            $this->publishes([__DIR__.'/../resource/lang' => resource_path('lang')], 'touge-swoole-server-lang');
        }
    }

    /**
     * Register the provided services.
     */
    public function register()
    {
        $this->app->register(ConsoleServiceProvider::class);
        $this->app->register(WebSocketServiceProvider::class);
    }

    /**
     * Define the "api" routes for the module.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::group([
            'middleware' => 'api',
            'namespace'  => $this->namespace,
            'prefix'     => 'api',
            'as'=> 'swoole.'
        ], function () {
            require __DIR__ . '/../routes/api.php';
        });
    }


    /**
     * load config file
     * @param $file
     */
    protected function loadConfig(){
        $key = substr($this->config_file, 0, -4);
        $full_path= __DIR__ . '/../config/' . $this->config_file;
        $this->app['config']->set($key, array_merge_recursive(config($key, []), require $full_path));
    }
}