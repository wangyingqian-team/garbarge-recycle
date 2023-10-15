<?php

namespace App\Models;

use App\Supports\Constant\GarbageRecycleConst;
use Illuminate\Database\Eloquent\Model;

class GarbageRecycleOrderModel extends Model
{
    protected $table = 'garbage_recycle_order';

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

    public function recycler()
    {
        return $this->hasOne(RecyclerModel::class, 'id', 'recycler_id');
    }

    public function village()
    {
        return $this->hasOne(VillageModel::class, 'id', 'village_id');
    }

    public function items()
    {
        return $this->hasMany(GarbageRecycleOrderItemsModel::class, 'order_no', 'order_no');
    }
}