<?php
namespace App\Models;

use App\Supports\Constant\GarbageRecycleConst;
use Illuminate\Database\Eloquent\Model;

class GarbageRecycleRateModel extends Model
{
    protected $table = 'garbage_recycle_rate';

    public $timestamps = false;

    protected $casts = [
        'image' => 'array'
    ];

    protected $appends = ['type_zh'];

    public function order()
    {
        return $this->hasOne(GarbageRecycleOrderModel::class, 'order_no', 'order_no');
    }

    public function getTypeZhAttribute()
    {
        return GarbageRecycleConst::GARBAGE_RECYCLE_RATE_TYPE_MAP[$this->attributes['type']];
    }
}