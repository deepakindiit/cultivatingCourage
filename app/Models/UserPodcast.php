<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPodcast extends Model
{
    use HasFactory;
    protected $table = 'user_podcasts';
    protected $fillable = ['title','thumbnail_image','audio'];
}
