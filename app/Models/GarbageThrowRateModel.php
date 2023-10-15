<?php

namespace App\Models;

use App\Supports\Constant\GarbageThrowConst;
use Illuminate\Database\Eloquent\Model;

class GarbageThrowRateModel extends Model
{
    protected $table = 'garbage_throw_rate';

    public $timestamps = false;

    protected $casts = [
        'image' => 'array'
    ];

    protected $appends = ['type_zh'];

    public function order()
    {
        return $this->hasOne(GarbageThrowOrderModel::class, 'order_no', 'order_no');
    }

    public function getTypeZhAttribute()
    {
        return GarbageThrowConst::GARBAGE_THROW_RATE_TYPE_MAP[$this->attributes['type']];
    }
}