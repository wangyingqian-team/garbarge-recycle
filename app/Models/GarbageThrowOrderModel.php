<?php

namespace App\Models;

use App\Supports\Constant\GarbageThrowConst;
use Illuminate\Database\Eloquent\Model;

class GarbageThrowOrderModel extends Model
{
    protected $table = 'garbage_throw_order';

    public $timestamps = false;

    protected $appends = ['status_zh'];

    public function getStatusZhAttribute()
    {
        return GarbageThrowConst::GARBAGE_THROW_ORDER_STATUS_MAP[$this->attributes['status']];
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
}