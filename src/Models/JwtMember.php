<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 2019-12-19
 * Time: 11:06
 */
namespace Touge\SwooleSocketServer\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;


class JwtMember extends Authenticatable implements JWTSubject
{
    use Notifiable;

    public function __construct(array $attributes = [])
    {
        $this->setTable(config('laravel-jwt-auth.database.auth_table'));
        Log::info(config('laravel-jwt-auth.database.auth_table'));
        parent::__construct($attributes);
    }

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    // Rest omitted for brevity

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}