<?php

namespace App\Console\Commands;

use App\Models\CouponRecordModel;
use Illuminate\Console\Command;

/**
* 优惠券自动过期
*/
class CouponExpire extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'garbage:coupon_expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '优惠券自动过期';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $ids = [];

        $coupons = CouponRecordModel::query()->where(['status' => 1])->get()->toArray();
        foreach ($coupons as $coupon) {
            if ($coupon['expire_time'] <= time()) {
                array_push($ids, $coupon['id']);
            }
        }

        if (empty($ids)) {
            CouponRecordModel::query()->whereIn('id', $ids)->update(['status' => 4]);
        }

        return true;
    }
}
