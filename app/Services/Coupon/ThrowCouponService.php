<?php

namespace App\Services\Coupon;

use App\Dto\UserThrowCouponLogDto;
use App\Exceptions\RestfulException;
use App\Supports\Constant\CouponConst;
use Carbon\Carbon;

class ThrowCouponService
{
    /**
     * 领取
     *
     * @param $userId
     * @param string $remark
     * @return bool
     */
    public function createCoupon($userId, $origin, $remark = '')
    {
        $data = [
            'user_id' => $userId,
            'status' => CouponConst::THROW_WAIT_STATUS,
            'origin' => $origin,
            'remark' => $remark,
            'begin_time' => Carbon::today()->toDateTimeString(),
            'end_time' => Carbon::tomorrow()->toDateTimeString()
        ];
        return app(UserThrowCouponLogDto::class)->create($data);
    }

    /**
     * 使用
     *
     * @param $id
     * @return int
     */
    public function UseCoupon($id)
    {
        $coupon = app(UserThrowCouponLogDto::class)->getCouponInfo($id);
        if ($coupon['status'] != CouponConst::THROW_WAIT_STATUS) {
            throw new RestfulException('代扔券状态错误');
        }
        if ($coupon['begin_time'] > Carbon::now()) {
            throw new RestfulException('代扔券未到使用时间');
        }
        if ($coupon['end_time'] < Carbon::now()) {
            throw new RestfulException('代扔券超过使用时间');
        }

        return app(UserThrowCouponLogDto::class)->updateStatusById($id, CouponConst::THROW_USED_STATUS);
    }


    /**
     * 过期
     *
     * @param $id
     * @return int
     */
    public function expireCoupon($id)
    {
        return app(UserThrowCouponLogDto::class)->updateStatus($id, CouponConst::THROW_EXPIRE_STATUS);
    }

    /**
     * 批量过期
     *
     * @return int
     */
    public function expireCoupons()
    {
        return app(UserThrowCouponLogDto::class)->expire();
    }

    /**
     * 作废
     *
     * @param $Id
     * @return int
     */
    public function freezeCoupon($id)
    {
        return app(UserThrowCouponLogDto::class)->updateStatus($id, CouponConst::THROW_FREEZE_STATUS);
    }

    /**
     * 获取代扔券列表
     *
     * @param $userId
     * @param $status
     * @return array
     */
    public function getCouponList($userId, $status)
    {
        return app(UserThrowCouponLogDto::class)->getCouponList($userId, $status);
    }

    /**
     * 根据优惠券id批量获取优惠券信息.
     *
     * @param array $couponIds
     * @param int $couponStatus
     *
     * @return array
     *
     */
    public function getCouponListByIds($couponIds, $couponStatus)
    {
        $couponList = app(UserThrowCouponLogDto::class)->getCouponListByIds($couponIds, $couponStatus);
        return array_column($couponList, null, 'id');
    }
}
