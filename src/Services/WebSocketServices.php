<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 2020-03-06
 * Time: 16:43
 */

namespace Touge\SwooleSocketServer\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Tymon\JWTAuth\Facades\JWTAuth;

class WebSocketServices
{
    protected $socket_server;

    protected $redis_server;
    /**
     * 初始化服务器
     */
    public function run(){
        $this->output("Starting service...");
        $this->initialization()->listener()->server_start();
    }

    /**
     * @return mixed
     */
    protected function server_start(){
        sleep(1);
        $this->output('Websocket start successful.');
        return $this->socket_server->start();
    }

    /**
     * @return $this
     */
    protected function listener(){
        sleep(1);
        $this->output("lsnrctl start");

        /**
         * 链接成功
         */
        $this->socket_server->on('open', function (\Swoole\WebSocket\Server $server, $frame) {
            $this->on_open( $frame);
        });

        $this->socket_server->on('message', function (\Swoole\WebSocket\Server $server, $frame){
            $this->on_message($server, $frame);
        });
        $this->socket_server->on('close', function(\Swoole\WebSocket\Server $server, $fd){
            $this->on_close($server, $fd);
        });
        $this->socket_server->on('request', function (Swoole\Http\Request $request, Swoole\Http\Response $response) {
            //see http://wiki.swoole.com/#/websocket_server
        });
        return $this;
    }

    /**
     * @param $frame
     */
    protected function on_open($frame){
        $response_message= $this->compress_message('connect_success', ["id"=>$frame->fd]);
        $this->socket_server->push($frame->fd, $response_message);
    }

    /**
     * @param $server
     * @param $fd
     */
    protected function on_close($server, $fd)
    {
        echo "client-{$fd} is closed\n";
    }

    /**
     * 删除用户
     * @param $room
     * @param $user_id
     */
    protected function delRoomUser($room, $user_id){
        $this->redis_server->hdel($room, $user_id);
    }

    /**
     * 用户加入到redis hget房间列表中
     * @param $user
     */
    protected function userApplyRoom($user)
    {
        $this->redis_server->hset($user['room'], $user['id'], json_encode($user));
    }

    /**
     * 向客户端应答消息
     * @param $fd 客户ID
     * @param $cmd 应答信令
     * @param $data 应答数据
     */
    protected function reply_message($fd, $cmd ,$data)
    {
        $replay_data= $this->compress_message($cmd, $data);
        $this->socket_server->push($fd, $replay_data);
    }


    /**
     * 获得用户信息
     *
     * @param $room
     * @param $user_id
     * @return mixed
     */
    protected function getRdsUser($room, $user_id){
        return $this->redis_server->hget($room, $user_id);
    }

    /**
     * Redis 指定房间的用户列表
     *
     * @param $room
     * @return mixed
     */
    protected function getRdsRoomUsers($room){
        return $this->redis_server->hvals($room);

    }

    /**
     * Redis 得到指定房间的fd列表
     * @param $room
     * @return array
     */
    protected function getRdsRoomFds($room){
        $room_users= $this->getRdsRoomUsers($room);

        $room_fds= [];
        foreach($room_users as $room_user){
            $user= json_decode($room_user, true);
            array_push($room_fds, $user['fd']);
        }
        return $room_fds;
    }

    /**
     * 消息广播
     * @param $fds
     * @param $data
     * @param string $cmd
     */
    protected function broadcast($fds ,$data ,$cmd='public')
    {
        foreach ($this->socket_server->connections as $fd){
            if(in_array($fd, $fds)){
                $this->reply_message($fd, $cmd ,$data);
            }
        }
    }


    /**
     * 组织公共消息
     *
     * @param string $cmd
     * @param $data
     */
    protected function __public($data){
        $user_id= $data['sender_id'];
        $room= $data['room'];
        $rds_user= $this->getRdsUser($room, $user_id);
        $fromUser= json_decode($rds_user ,TRUE);
        $room_fds= $this->getRdsRoomFds($fromUser['room']);

        $options= [
            "sender"=> ['id'=>$user_id, 'name'=> $fromUser['name'] ,'identity'=> $fromUser['identity']],
            "message"=> $data['message']
        ];
        $this->broadcast($room_fds, $options, 'public');
    }


    /**
     * 验明正身
     */
    protected function __verify($data ,$fd)
    {
        $token= $data['token'];
        $room= $data['room'];

        $user= $this->getMember($token);

        $user['room']= $room;
        $user['fd']= $fd;

        $this->userApplyRoom($user);

        $room_fds= $this->getRdsRoomFds($user['room']);

        $data= [
            "sender"=> ['id'=>0, 'name'=> '系统'],
            "message"=> "欢迎 {$user['name']} 的到来！"
        ];
        $this->broadcast($room_fds, $data ,'broadcast');
    }


    /**
     * 离开了，再见
     * @param $data
     */
    protected function __leave($data, $fd){
        $user_id= $data['sender_id'];
        $room= $data['room'];

        $rds_user= $this->getRdsUser($room, $user_id);


        $fromUser= json_decode($rds_user ,TRUE);


        $room_fds= $this->getRdsRoomFds($fromUser['room']);

        $data= [
            "sender"=> ['id'=>0, 'name'=> '系统'],
            "message"=> "{$fromUser['name']} 离开了！"
        ];
        $this->broadcast($room_fds, $data ,'broadcast');

        //关闭他的链接
        $this->socket_server->close($fd);

        //删除他的用户信息
        $this->delRoomUser($room, $user_id);

    }

    /**
     * 收到消息时的处理
     *
     * @param $server
     * @param $frame
     */
    protected function on_message($server, $frame){
        $response= $this->unpack_message($frame->data);
        switch ($response['cmd']){
            case "verify":
                $this->__verify($response['data'], $frame->fd);
            break;
            case 'public':
                $this->__public($response['data']);
                break;
            case 'leave'://用户离开，关闭当前链接并删除用户
                $this->__leave($response['data'], $frame->fd);
            break;
        }
    }



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



    /**
     * 用户加入聊天室
     *
     * @param $member
     * @param $room
     * @param $fd
     * @return bool
     */
    protected function memberApplyRooms($member ,$room ,$fd)
    {
        $member_id= $member['id'];

        $member['fd']= $fd;
        $member['room']= $room;
        $this->members[$member_id]= $member;

        if( !in_array($room, $this->rooms) ){
            $this->rooms[$room][]= $member;
        }else{
            array_push($this->rooms[$room], $member);
        }
    }



    /**
     * 初始化并启动websocket
     *
     * @return $this
     */
    protected function initialization()
    {
        $this->output("Initialize system configuration...");

        sleep(1);
        try{
            $redis_config= config('database.redis.touge_live');
            $message= "Link to redis server. host: {$redis_config['host']} ,port: {$redis_config['port']}";
            $this->output($message);
            $this->redis_server= Redis::connection('touge_live');
        }catch (\Exception $exception){
            die($exception->getMessage());
        }

        sleep(1);

        $config_socket= config('touge-swoole-server.socket');
        $message= "Instantiate websocket server. host: {$config_socket['host']}, port: {$config_socket['port']}";
        $this->output($message);

        $this->socket_server = new \Swoole\WebSocket\Server($config_socket['host'], $config_socket['port']);

        return $this;
    }

    /**
     * 输入到控制台
     * @param $message
     */
    protected function output($message)
    {
        echo  date('Y-m-d H:i:s') . " -- " .$message . "\n";
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
     * @param $token
     * @return mixed
     */
    protected function getMember($token){

        $request= $this->applyTokenToRequest($token);
        JWTAuth::setRequest($request)->parseToken();
        $user = JWTAuth::parseToken()->authenticate();

        return [
            'id'=> $user->id,
            'name'=> $user->name,
            'identity'=> $user->identity,
        ];

    }
}