<?php


namespace App\Services\Activity;

use App\Exceptions\RestfulException;
use App\Models\CouponRecordModel;
use App\Models\InvitationRecordModel;
use App\Models\InvitationRelationModel;
use App\Models\UserAssetsModel;
use App\Models\UserModel;
use App\Supports\Constant\ActivityConst;
use App\Supports\Constant\UserConst;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * 抽奖活动
 *
 * Class InvitationService
 * @package App\Services\Activity
 */
class CouponService
{
    /**
     * 获取优惠券
     *
     * @param $userId
     * @param $id
     * @return bool
     */
    public function obtainCoupon($userId, $id, $expire)
    {
        return CouponRecordModel::query()->insert([
            'user_id' => $userId,
            'coupon_id' => $id,
            'expire_time' => $expire
        ]);
    }

    /**
     * 使用优惠券
     *
     * @param $userId
     * @param $id
     * @param $orderOn
     * @return bool
     */
    public function useCoupon($userId, $id, $orderOn)
    {
        $coupon = CouponRecordModel::query()->where(['user_id' => $userId, 'id' => $id, 'status' => 1])->macroFirst();
        if (empty($coupon)){
            throw new RestfulException('话费券使用异常，请稍后再试！');
        }

        CouponRecordModel::query()->where('id', $id)->update(['status' => 2, 'order_no' => $orderOn, 'used_time' => time()]);

        return true;
    }

    /**
     * 核销优惠券
     *
     * @param $id
     * @return int
     */
    public function verifyCoupon($id)
    {
        return CouponRecordModel::query()->whereKey($id)->update(['status' => 3]);
    }

    /**
     * 优惠券详情
     *
     * @param $id
     * @return mixed
     */
    public function getCouponDetail($id)
    {
        return CouponRecordModel::query()->whereKey($id)->macroFirst();
    }

    /**
     * 优惠券列表
     *
     * @param $userId
     * @param $type
     * @param $page
     * @param $limit
     * @return mixed
     */
    public function getCouponList($userId, $type, $page, $limit)
    {
        $where = [
            'user_id' => $userId,
            'type' => $type
        ];
        return CouponRecordModel::query()->macroQuery($where, ['*'], ['create_time'=>'desc'], $page, $limit, true);
    }
}
