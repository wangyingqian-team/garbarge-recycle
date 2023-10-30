<?php


namespace App\Services\Activity;

use App\Exceptions\RestfulException;
use App\Models\InvitationRecordModel;
use App\Models\InvitationRelationModel;
use App\Models\UserAssetsModel;
use App\Supports\Constant\ActivityConst;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * 新人福利活动
 *
 * Class InvitationService
 * @package App\Services\Activity
 */
class NewerService
{
    const NEWER_SECOND = 2592000; //新人身份保持30天

    /**
     * 新人身份
     *
     * @param $userId
     * @return bool
     */
    public function newer($userId)
    {
        $t = now()->addSeconds(self::NEWER_SECOND)->timestamp;
        $redis = Redis::connection('activity');
        $redis->hset('newer_identity', $userId, $t);
        return true;
    }

    /**
     * 首单奖励
     *
     * @param $userId
     * @param $orderAmount
     */
    public function firstOrder($userId, $orderAmount)
    {
        //todo查询是否有订单记录

        //订单门槛金额 => 代金券id
        $amount = [
            100 => 15,
            80 => 14,
            50 => 13,
            20 => 12,
            10 => 11,
        ];
        /** @var CouponService $couponService */
        $couponService = get_service(CouponService::class);
        foreach ($amount as $a => $id) {
            if ($orderAmount >= $a) {
                $couponService->obtainCoupon($userId, $id, self::NEWER_SECOND);
                break;
            }
        }
    }
}
