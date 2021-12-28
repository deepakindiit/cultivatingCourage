<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;


class User extends Authenticatable implements JWTSubject
{
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
    
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;

    protected $table = 'users';
    
    protected $fillable = ['name','email','password','image','otp','status','remember_token','address','push_notification_status'];

}

