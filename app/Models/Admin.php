<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    protected $table = 'admins';
    protected $fillable = ['full_name','email','phone_no','image','password','remember_token','status'];

	protected $hidden = [
        'password', 'remember_token',
    ];

}
