<?php

namespace App\Models;

use App\Supports\Constant\ JifenConst;
use Illuminate\Database\Eloquent\Model;

class JifenOrderModel extends Model
{
    protected $table = 'jifen_order';
    public $timestamps = false;
    protected $appends = ['status_zh', 'delivery_zh'];

    public function getStatusZhAttribute()
    {
        return  JifenConst::JI_FEN_ORDER_STATUS_MAP[$this->attributes['status']];
    }

    public function getDeliveryZhAttribute()
    {
        return  JifenConst::JI_FEN_DELIVERY_MAP[$this->attributes['delivery_type']];
    }
}
