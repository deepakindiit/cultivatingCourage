<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommonPage extends Model
{
    use HasFactory;

    protected $table = 'common_pages';
    protected $fillable = ['type','title','short_description','description','video','image'];
}




