<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 2019-12-18
 * Time: 16:22
 */

namespace Touge\SwooleSocketServer\Traits;


use Touge\SwooleSocketServer\Exceptions\ResponseFailedException;

trait ApiResponse
{
    /**
     * @param $data
     * @param string $status
     * @param int $httpCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function response($data,$status='successful',$httpCode=200){
        $output = [
            'status'=>$status
        ];

        if($status=='successful'){
            $output['data'] = $data;
        }else{
            $output['message'] = $data;
        }
        return response()->json($output,$httpCode)
            ->setEncodingOptions(JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param $message
     * @param int $httpCode
     * @throws ResponseFailedException
     */
    protected function failed($message,$httpCode=200){
        if(is_array($message)){
            $message= json_encode($message,JSON_UNESCAPED_UNICODE);
        }
        throw new ResponseFailedException($message ,$httpCode);
    }

    /**
     * @param $data
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function success($data,$code=200){
        return $this->response($data,'successful',$code);
    }
}