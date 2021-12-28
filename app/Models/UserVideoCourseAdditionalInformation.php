<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserVideoCourseAdditionalInformation extends Model
{
    use HasFactory;

    protected $table = 'user_video_course_additional_informations';
    protected $fillable = ['user_video_course_id','additional_information_title','additional_information_detail'];

}
