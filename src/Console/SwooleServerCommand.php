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
    }
}
