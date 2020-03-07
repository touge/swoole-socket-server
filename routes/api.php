<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 2019-12-18
 * Time: 17:00
 */

use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Router;

Route::group([
    'prefix'=>'auth',
    'namespace'=> 'Api',
    'as'=> 'auth.'
],function (Router $router){
    $router->post('login', 'AuthController@login')
        ->name('login');
    $router->post('logout', 'AuthController@logout')
        ->name("logout");
    $router->post('refresh', 'AuthController@refresh')
        ->name("refresh");
    $router->get('me', 'AuthController@me')
        ->name("me");
});
