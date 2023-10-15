<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecyclerModel extends Model
{
    protected $table = 'recycler';
    public $timestamps = false;

    public function userInfo() {
        return $this->belongsTo(UserModel::class, 'user_id', 'id');
    }

    public function assets() {
        return $this->hasOne(RecyclerAssetsModel::class, 'recycler_id', 'id');
    }
}
