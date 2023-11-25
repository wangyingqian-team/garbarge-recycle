<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAssetsModel extends Model
{
    protected $table = 'user_assets';

    public $timestamps = false;

    public function userInfo ()
    {
        return $this->belongsTo(UserModel::class, 'user_id', 'id');
    }
}
