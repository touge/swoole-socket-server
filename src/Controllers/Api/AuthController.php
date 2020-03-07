<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 2019-12-19
 * Time: 09:13
 */
namespace Touge\SwooleSocketServer\Controllers\Api;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Touge\SwooleSocketServer\Traits\ApiResponse;

class AuthController extends Controller
{
    use ApiResponse;

    protected $guard_name= 'laravel-jwt';

    /**
     * Create a new AuthController instance.
     * 要求附带email和password（数据来源users表）
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('jwt.auth', ['except' => ['login']]);
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|void
     * @throws \Touge\JwtAuth\Exceptions\ResponseFailedException
     */
    public function login(Request $request)
    {
        $credentials = $request->only([$this->username(), 'password']);
        if (! $token = $this->guard()->attempt($credentials))
        {
            return $this->failed(__('JwtAuth::jwt-auth.login-failed'));
        }

        $token = $this->guard()->attempt($credentials);
        $login_user= $this->guard()->user();
        if( $login_user->expire_time && strtotime($login_user->expire_time) < time() )
        {
            $this->failed(__('JwtAuth::jwt-auth.login-expire'));
        }

        return $this->success($this->respondWithToken($token));
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return $this->success($this->guard()->user());
    }

    /**
     * 用户退出
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(){
        $this->guard()->logout();
        return $this->success([]);
    }

    /**
     * Get the token array structure.
     *
     * @param $token
     * @return array
     */
    protected function respondWithToken($token): array
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth($this->guard_name)->factory()->getTTL() * 60
        ];
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    protected function username()
    {
        return 'email';
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard($this->guard_name);
    }
}