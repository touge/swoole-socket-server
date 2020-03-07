<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 2019-12-26
 * Time: 15:44
 */

namespace Touge\JwtAuth\Controllers\Api;


use App\Http\Controllers\Controller;
use Touge\JwtAuth\Traits\ApiResponse;

class TestController extends Controller
{
    use ApiResponse;


    public function index(){
        return $this->success(['example']);
    }
}