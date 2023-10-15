<?php

namespace App\Models;

use App\Supports\Constant\CouponConst;
use Illuminate\Database\Eloquent\Model;

class UserThrowCouponLogModel extends Model
{
    protected $table = 'user_throw_coupon_log';
    public $timestamps = false;
    protected $appends = ['origin_zh'];

    public function getOriginZhAttribute()
    {
        return CouponConst::THROW_ORIGIN_MAPS[$this->attributes['origin']];
    }
}
