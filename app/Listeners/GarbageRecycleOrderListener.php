<?php

namespace App\Listeners;

use App\Events\GarbageRecycleOrderCancelEvent;
use App\Events\GarbageRecycleOrderCreateEvent;
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
        $reservedOrderCountKey = RedisKeyConst::RECYCLE_RESERVED_ORDER_COUNT_TODAY;
        $redis->incr($reservedOrderCountKey);

        // 指定回收员在指定时间段的回收单数+1（Redis实现，注意如果用户取消或系统取消需要回退对应的数量）
        $recyclerId = $data['recycler_id'];
        $throwDate = $data['recycle_date'];
        $timePriod = $data['time_period'];
        $recyclerRecycleCountKey = RedisKeyConst::RECYCLE_RECYCLER_ORDER_COUNT_PREFIX . $recyclerId . ':' . $throwDate;
        $redis->hincrby($recyclerRecycleCountKey, $timePriod, 1);
    }

    /**
     * 订单取消事件监听者.
     *
     * @param GarbageRecycleOrderCancelEvent $event
     */
    public function cancelRecycleOrder(GarbageRecycleOrderCancelEvent $event)
    {
        $data = $event->data;

        $recyclerId = $data['recycler_id'];
        $throwDate = $data['recycle_date'];
        $timePriod = $data['time_period'];

        // 指定回收员在指定时间段的回收单数-1
        $redis = Redis::connection('recycle');
        $recyclerThrowCountKey = RedisKeyConst::RECYCLE_RECYCLER_ORDER_COUNT_PREFIX . $recyclerId . ':' . $throwDate;
        $redis->hincrby($recyclerThrowCountKey, $timePriod, -1);
    }
}