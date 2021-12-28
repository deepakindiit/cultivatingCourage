<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserVideoCourseWorkingDetail extends Model
{
    use HasFactory;

    protected $table = 'user_video_course_working_details';
    protected $fillable = ['user_video_course_id','working_detail'];
}
