<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 2019-12-19
 * Time: 09:27
 */
return [
    'redis'=> [
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'port'=> env('REDIS_PORT', 6379),
        'password'=> env('REDIS_PASSWORD', null),
        'database'=> 0
    ],
    'socket'=>[
        'host'=> env('SWOOLE_SOCKET_SERVER_HOST', '0.0.0.0'),
        'port'=> env('SWOOLE_SOCKET_SERVER_PORT' ,'888'),
    ]
];
