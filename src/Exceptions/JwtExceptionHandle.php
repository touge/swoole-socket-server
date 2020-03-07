<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 2019-12-26
 * Time: 11:00
 */

namespace Touge\SwooleSocketServer\Exceptions;


use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class JwtExceptionHandle
{
    protected $exception;

    protected $messages= [
        'Token not provided'=> '未提供令牌',
        'Token has expired'=> '令牌已过期',
        'User not found'=> '找不到用户',
        'An error occurred'=> '发生系统错误',
        'Wrong number of segments'=> '令牌解析错误',
        'Token Signature could not be verified.'=> '无法验证令牌签名',
        'The token has been blacklisted'=> '令牌已被列入黑名单',
    ];

    public function __construct(\Exception $exception)
    {
        $this->exception= $exception;
        $this->check();
    }

    public function check(){
        if(!$this->isJwtException()){
            return true;
        }

        $this->throw_error($this->trans_auth_message($this->exception->getMessage()));
    }


    protected function isJwtException()
    {
        if($this->exception instanceof UnauthorizedHttpException)
        {
            $headers= $this->exception->getHeaders();
            if(key_exists('WWW-Authenticate', $headers) && $headers['WWW-Authenticate'] === 'jwt-auth'){
                return true;
            }
        }
    }


    /**
     *
     * @param $message
     * @throws ResponseFailedException
     */
    protected function throw_error($message){
        throw new ResponseFailedException($message, 401);
    }

    /**
     * @param $key
     * @return array|string|null
     */
    protected function trans_auth_message($key)
    {
        if (config('app.locale') == 'zh-CN' && key_exists($key, $this->messages)){
            return $this->messages[$key];
        }
        return $key;
    }
}