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
        $this->app['config']->set('database.redis.touge_live', config('touge-swoole-server.redis'));


        /**
         * 发布资源内容
         */
        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__.'/../config' => config_path()], 'touge-swoole-server-config');
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
     * load config file
     * @param $file
     */
    protected function loadConfig(){
        $key = substr($this->config_file, 0, -4);
        $full_path= __DIR__ . '/../config/' . $this->config_file;
        $this->app['config']->set($key, array_merge_recursive(config($key, []), require $full_path));

    }
}