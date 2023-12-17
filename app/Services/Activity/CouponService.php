<?php


namespace App\Services\Activity;

use App\Exceptions\RestfulException;
use App\Models\CouponModel;
use App\Models\CouponRecordModel;
use App\Models\InvitationRecordModel;
use App\Models\InvitationRelationModel;
use App\Models\UserAssetsModel;
use App\Models\UserModel;
use App\Services\GarbageRecycle\GarbageRecycleOrderService;
use App\Supports\Constant\ActivityConst;
use App\Supports\Constant\GarbageRecycleConst;
use App\Supports\Constant\RedisKeyConst;
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
            'expire_time' => $expire,
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
        return CouponModel::query()->whereKey($id)->macroFirst();
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
    public function getCouponList($userId, $status = null, $type = null)
    {
        $where = [
            'user_id' => $userId,
        ];
        $type && $where['type'] = $type;
        $status && $where['status'] = $status;

        return CouponRecordModel::query()->macroQuery($where, ['*', 'coupon.*']);
    }

    /**
     * 福利中心数据获取.
     *
     * @param $userId int 用户ID
     *
     * @return array 福利中心详细数据
     */
    public function welfareCenter($userId)
    {
        // 首单福利
        // 判断有没有首单，以及首单福利是否已经领取
        $hasFirstOrder = false;
        $hasReceiveFirstOrderWelfare = false;
        $checkFirstOrder = app(GarbageRecycleOrderService::class)->getGarbageRecycleOrderInfo([
            'user_id' => $userId,
            'status' => GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_FINISHED
        ]);
        if (!empty($checkFirstOrder)) {
            $hasFirstOrder = true;
        }

        // 判断是否领取对应的券，直接查询coupon_record表.
        $checkFirstOrderReceived = CouponRecordModel::query()->where([
            'user_id' => $userId,
            'coupon_id' => ActivityConst::FIRST_ORDER_COUPON_ID
        ])->count();
        if ($checkFirstOrderReceived > 0) {
            $hasReceiveFirstOrderWelfare = true;
        }

        if (!$hasFirstOrder) {
            $firstOrderWelfareText = '完成新订单领取首单福利';
        } else if ($hasReceiveFirstOrderWelfare) {
            $firstOrderWelfareText = '已领取';
        } else {
            $firstOrderWelfareText = '领取';
        }

        // 多卖多送
        // 计算用户当前已经完成的订单数.
        $userOrderCount = app(GarbageRecycleOrderService::class)->getGarbageRecycleOrderCount([
            'user_id' => $userId,
            'status' => GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_FINISHED
        ]);

        // 遍历多卖多送规则数组，判断用户是否满足领券条件以及有没有领取相应的券.
        $sellGiveDetails = [];
        foreach (ActivityConst::SELL_GIVE_COUPON_RULES as $rule) {
            $orderQuantity = $rule['order_quantity'];
            $couponId = $rule['coupon_id'];
            $couponName = $rule['coupon_name'];
            if ($userOrderCount >= $orderQuantity) {
                $checkSellGiveReceived = CouponRecordModel::query()->where([
                    'user_id' => $userId,
                    'coupon_id' => $couponId
                ])->count();
                if ($checkSellGiveReceived > 0) {
                    $welfareText = '已领取';
                } else {
                    $welfareText = '领取';
                }
            } else {
                $welfareText = '完成' . ($orderQuantity - $userOrderCount) . '次订单领取';
            }

            $sellGiveDetails[] = [
                'coupon_name' => $couponName,
                'welfare_text' => $welfareText
            ];
        }

        return [
            'first_order_details' => [
                'coupon_name' => ActivityConst::FIRST_ORDER_COUPON_NAME,
                'welfare_text' => $firstOrderWelfareText
            ],
            'sell_give_details' => $sellGiveDetails
        ];

    }
}
