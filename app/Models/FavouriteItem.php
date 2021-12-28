<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CommonPage;

class FavouriteItem extends Model
{
    use HasFactory;
    protected $table    = 'favourite_items';
    protected $fillable = ['user_id','favourite_item_status','type','type_id'];

    public function CommonPage()
    {
        return $this->hasOne('App\Models\CommonPage', 'id', 'type_id');
    }
}
