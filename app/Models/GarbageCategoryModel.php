<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GarbageCategoryModel extends Model
{
    protected $table = 'garbage_category';

    public $timestamps = false;

    public function type()
    {
        return $this->hasMany(GarbageTypeModel::class, 'category_id', 'id');
    }

}
