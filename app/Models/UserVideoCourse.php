<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\VideoCategory;
use App\Models\UserVideoCourse;
use App\Models\UserVideoCourseAdditionalInformation;
use App\Models\UserVideoCourseWorkingDetail;


class UserVideoCourse extends Model
{
    use HasFactory;

    protected $table = 'user_video_courses';
    protected $fillable = ['thumbnail_image','video','video_title','video_description','video_category_id','workout_detail'];

    public function videoCourseWorkingDetail(){
        return $this->hasMany('App\Models\UserVideoCourseWorkingDetail','user_video_course_id', 'id');
    }

    public function videoCourseAdditionalInformation(){
        return $this->hasMany('App\Models\UserVideoCourseAdditionalInformation', 'user_video_course_id', 'id');
    }

    public function videoCategory(){
        return $this->belongsTo('App\Models\VideoCategory','video_category_id','id');
    }

}
