<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRecipieIngredient extends Model
{
    use HasFactory;
    protected $table = 'user_recipies_ingredients';
    protected $fillable = ['user_recipies_id','ingredient_name'];
}
