<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GarbageTypeModel extends Model
{
    protected $table = 'garbage_type';

    public $timestamps = false;

    public function category()
    {
        return $this->hasOne(GarbageCategoryModel::class, 'id', 'category_id');
    }

}