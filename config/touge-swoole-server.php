<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 2019-12-19
 * Time: 09:27
 */
return [
    'auth' => [
        'defaults'=> [
            'guard'=> 'laravel-jwt'
        ],
        'guards' => [
            'laravel-jwt' => [
                'driver'=> 'jwt',
                'provider'=> 'jwt-member'
            ],
        ],
        'providers' => [
            'jwt-member'=>[
                'driver' => 'eloquent',
                'model'=>\Touge\JwtAuth\Models\JwtMember::class
            ],
        ],
    ],
    'database'=> [
        'auth_table'=> 'customer_school_members',
    ]
];