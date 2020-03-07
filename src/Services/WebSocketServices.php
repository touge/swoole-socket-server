<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 2020-03-06
 * Time: 16:43
 */

namespace Touge\SwooleSocketServer\Services;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class WebSocketServices
{
    public function server(){

//        JWT::setRequest($request);
//        JWTAuth::parseToken();
        $this->initialization();
    }


    /**
     * @param $string
     * @return mixed
     */
    protected function json2array($string){
        return json_decode($string, TRUE);
    }

    protected $server;

    protected static $CMD= [
        'verify','join'
    ];

    /**
     * 回复的消息
     * @param $cmd
     * @param $data
     * @return false|string
     */
    protected function compress_message($cmd, $data){
        $options= [
            "cmd"=> $cmd,
            "data"=> $data
        ];
        return json_encode($options, JSON_UNESCAPED_UNICODE);
    }

    /**
     *
     * 收到的消息
     * @param $data
     * @return mixed
     */
    protected function unpack_message($data){
        return json_decode($data, TRUE);
    }

    protected $members= [];

    /**
     * 向客户端应答消息
     * @param $fd 客户ID
     * @param $cmd 应答信令
     * @param $data 应答数据
     */
    protected function reply_message($fd, $cmd ,$data)
    {
        $replay_data= $this->compress_message($cmd, $data);
        $this->server->push($fd, $replay_data);
    }

    /**
     * 初始化并启动websocket
     *
     * @return $this
     */
    protected function initialization(){
        $this->server = new \Swoole\WebSocket\Server("0.0.0.0", 9501);

        /**
         * 链接成功
         */
        $this->server->on('open', function (\Swoole\WebSocket\Server $server, $frame) {
            $response_message= $this->compress_message('connect_success', ["id"=>$frame->fd]);
            $server->push($frame->fd, $response_message);
        });

        /**
         * 当收到消息
         */
        $this->server->on('message', function (\Swoole\WebSocket\Server $server, $frame) {
            try{
                $message= $this->unpack_message($frame->data);
                switch ($message['cmd']){
                    case "verify":
                        //将当前用户加入到聊天用户列表中
                        $token= $message['data']['token'];
                        $request= $this->applyTokenToRequest($token);
                        $member= $this->getMember($request);
                        array_push($this->members, [$frame->fd=> $member]);

                        $data= ["message"=> "欢迎 {$member['name']} 的到来！"];
                        foreach($server->connections as $fd)
                        {
                            $this->reply_message($fd,'broadcast', $data);
                        }
                    break;
                    case "public":
                        $data= $message["data"]["message"];
                        foreach($server->connections as $fd){
                            $this->reply_message($fd, 'public', $data);
                        }
                        break;
                }
            }catch (\Exception $exception){
                echo $exception->getMessage();
            }
        });

        $this->server->on('close', function ($ser, $fd) {
            echo "client {$fd} closed\n";
        });

        $this->server->on('request', function ($request, $response) {
            // 接收http请求从get获取message参数的值，给用户推送
            // $this->server->connections 遍历所有websocket连接用户的fd，给所有用户推送
            foreach ($this->server->connections as $fd) {
                // 需要先判断是否是正确的websocket连接，否则有可能会push失败
                if ($this->server->isEstablished($fd)) {
                    $this->server->push($fd, $request->get['message']);
                }
            }
        });
        $this->server->start();
    }


    /**
     * 生成一个request，并将token放入到header中
     *
     * @param $token
     * @return Request
     */
    protected function applyTokenToRequest($token)
    {
        $request= Request::capture();
        $request->headers->set('Authorization',"Bearer {$token}");
        return $request;
    }


    /**
     * @param Request $request
     * @return mixed
     */
    protected function getMember(Request $request){
        JWTAuth::setRequest($request)->parseToken();
        $user = JWTAuth::parseToken()->authenticate();

        return ['id'=> $user->id, 'name'=> $user->name];

    }
}