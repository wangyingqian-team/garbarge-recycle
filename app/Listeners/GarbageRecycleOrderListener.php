<?php

namespace App\Listeners;

use App\Events\GarbageRecycleOrderCancelEvent;
use App\Events\GarbageRecycleOrderCreateEvent;
use App\Events\GarbageRecycleOrderFinishEvent;
use App\Services\Activity\InvitationService;
use App\Services\GarbageRecycle\GarbageRecycleOrderService;
use App\Services\User\AssertService;
use App\Supports\Constant\ActivityConst;
use App\Supports\Constant\RedisKeyConst;
use Illuminate\Support\Facades\Redis;

/**
 * 回收订单事件监听器.
 */
class GarbageRecycleOrderListener
{
    /**
     * 订单创建事件监听者.
     *
     * @param GarbageRecycleOrderCreateEvent $event
     */
    public function createRecycleOrder(GarbageRecycleOrderCreateEvent $event)
    {
        $data = $event->data;

        // 统计：今天预约的回收单数+1（Redis实现，定时任务每天凌晨清理）
        $redis = Redis::connection('recycle');
//        $reservedOrderCountKey = RedisKeyConst::RECYCLE_RESERVED_ORDER_COUNT_TODAY;
//        $redis->incr($reservedOrderCountKey);

        // 回收员在指定时间段的回收单数+1（Redis实现，注意如果用户取消或系统取消需要回退对应的数量）
        $recyclingDate = $data['recycle_date'];
        $recyclePeriod = $data['recycle_period'];
        $recyclerOrderPeriodCountKey = RedisKeyConst::THROW_RECYCLER_ORDER_COUNT_PREFIX . $recyclingDate;
        $redis->hincrby($recyclerOrderPeriodCountKey, $recyclePeriod, 1);
    }

    /**
     * 订单取消事件监听者.
     *
     * @param GarbageRecycleOrderCancelEvent $event
     */
    public function cancelRecycleOrder(GarbageRecycleOrderCancelEvent $event)
    {
        $data = $event->data;

        $recyclingDate = $data['recycle_date'];
        $recyclePeriod = $data['recycle_period'];

        // 指定回收员在指定时间段的回收单数-1
        $redis = Redis::connection('recycle');
        $recyclerOrderPeriodCountKey = RedisKeyConst::THROW_RECYCLER_ORDER_COUNT_PREFIX . ':' . $recyclingDate;
        $redis->hincrby($recyclerOrderPeriodCountKey, $recyclePeriod, -1);

        // todo 修改优惠券状态
    }

    public function finishRecycleOrder(GarbageRecycleOrderFinishEvent $event)
    {
        $data = $event->data;

        // todo：订单完成后Redis里面存放公告信息(xxx在xxx完成，事先清除该userId的信息再添加，Redis里面保证只有userId不重复的20条数据)

        // 订单积分计算
        $orderNo = $data['order_no'];
        $userId = $data['user_id'];
        $recycleAmount = $data['recycle_amount'];
        $totalAmount = $data['total_amount'];
        app(AssertService::class)->increaseJifen($userId, $recycleAmount * ActivityConst::JIFEN_EXCHANGE_AMOUNT);

        // 分佣关系：如果有的话，生成绿豆
        $invitationInfo = app(InvitationService::class)->getUserInvitation($userId);
        if (!empty($invitationInfo)) {
            $superiorUserId = $invitationInfo['superior_id'];
            app(InvitationService::class)->getBean($userId, $superiorUserId, $orderNo, $recycleAmount);
        }

        // redis 更新用户收益
        $redis = Redis::connection('recycle');
        $userIncomeKey = RedisKeyConst::USER_INCOME;
        $redis->hincrby($userIncomeKey, $userId, $totalAmount);

        // 生成一条通知消息记录
        app(GarbageRecycleOrderService::class)->generateNoticeRecord($userId, $orderNo);
    }
}
