<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 2020-03-06
 * Time: 16:43
 */

namespace Touge\SwooleSocketServer\Facades;


use Illuminate\Support\Facades\Facade;

/**
 * Class WebSocket
 * @package Touge\SwooleSocketServer\Facades
 *
 * @method static \Touge\SwooleSocketServer\Services\WebSocketServices server()
 */
//* @method static \Touge\AdminExamination\Services\GroupPaperService  group_papers($group_id)

class WebSocket extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'websocket.server';
    }
}