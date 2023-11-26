<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CouponRecordModel extends Model
{

    protected $table = 'coupon_record';

    public $timestamps = false;

    public function coupon()
    {
        return $this->belongsTo(CouponModel::class, 'coupon_id', 'id');
    }

}
