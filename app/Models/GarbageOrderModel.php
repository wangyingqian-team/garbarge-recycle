<?php

namespace App\Models;

use App\Supports\Constant\GarbageRecycleConst;
use Illuminate\Database\Eloquent\Model;

class GarbageOrderModel extends Model
{
    protected $table = 'garbage_order';

    public $timestamps = false;

    protected $appends = ['status_zh'];

    public function getStatusZhAttribute()
    {
        return GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_MAP[$this->attributes['status']];
    }

    public function user()
    {
        return $this->hasOne(UserModel::class, 'id', 'user_id');
    }

    public function address()
    {
        return $this->hasOne(UserAddressModel::class, 'id', 'address_id');
    }

}
