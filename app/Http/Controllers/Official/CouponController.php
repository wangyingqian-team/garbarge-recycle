<?php

namespace App\Http\Controllers\Official;


use App\Services\Coupon\ThrowCouponService;

class CouponController extends BaseController
{

    public function getThrowCouponList() {
        $status = $this->request->get('status');
        return app(ThrowCouponService::class)->getCouponList($this->userId, $status);
    }

}
