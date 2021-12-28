<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\UserRecipieIngredient;

class UserRecipie extends Model
{
    use HasFactory;

    protected $table = 'user_recipies';
    protected $fillable = ['recipie_short_description','thumbnail_image','image','recipie_title','recipie_intro','description','cooking_detail'];

    public function recipieIngredients(){
        return $this->hasMany('App\Models\UserRecipieIngredient','user_recipies_id', 'id');
    }

}









