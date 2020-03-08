<?php

namespace Touge\SwooleSocketServer\Console;

use Illuminate\Console\Command;
use Touge\SwooleSocketServer\Facades\WebSocket;

class SwooleServerCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'touge:swoole-server';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'swoole server';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        WebSocket::run();

//        $ws = new Server("0.0.0.0", 9502);
//        //监听WebSocket连接打开事件
//        $ws->on('open', function ($ws, $request) {
//            $ws->push($request->fd, "hello, welcome\n");
//        });
//
//        //监听WebSocket消息事件
//        $ws->on('message', function ($ws, $frame) {
//            echo $frame->fd . "---Message: {$frame->data}\n";
//            $ws->push($frame->fd, "server: {$frame->data}");
//        });
//
//        //监听WebSocket连接关闭事件
//        $ws->on('close', function ($ws, $fd) {
//            echo "client-{$fd} is closed\n";
//        });
//        $ws->start();

    }
}
