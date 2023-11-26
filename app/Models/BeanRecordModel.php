<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BeanRecordModel extends Model
{
    protected $table = 'bean_record';
    public $timestamps = false;

    public function subUserInfo()
    {
        return $this->belongsTo(UserModel::class, 'user_id', 'id');
    }
}
