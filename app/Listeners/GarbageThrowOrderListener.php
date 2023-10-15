<?php
namespace App\Listeners;

use App\Events\GarbageThrowOrderCancelEvent;
use App\Events\GarbageThrowOrderCreateEvent;
use App\Supports\Constant\RedisKeyConst;
use Illuminate\Support\Facades\Redis;

/**
 * 代仍订单事件监听器.
 */
class GarbageThrowOrderListener
{
    /**
     * 订单创建事件监听者.
     *
     * @param GarbageThrowOrderCreateEvent $event
     */
    public function createThrowOrder(GarbageThrowOrderCreateEvent $event)
    {
        $data = $event->data;

        // 统计：今天预约的代仍单数+1（Redis实现，定时任务每天凌晨清理）
        $redis = Redis::connection('throw');
        $reservedOrderCountKey = RedisKeyConst::THROW_RESERVED_ORDER_COUNT_TODAY;
        $redis->incr($reservedOrderCountKey);

        // 指定回收员在指定时间段的代仍单数+1（Redis实现，注意如果用户取消或系统取消需要回退对应的数量）
        $recyclerId = $data['recycler_id'];
        $throwDate = $data['throw_date'];
        $timePriod = $data['time_period'];
        $recyclerThrowCountKey = RedisKeyConst::THROW_RECYCLER_ORDER_COUNT_PREFIX . $recyclerId . ':' . $throwDate;
        $redis->hincrby($recyclerThrowCountKey, $timePriod, 1);
    }

    /**
     * 订单取消事件监听者.
     *
     * @param GarbageThrowOrderCancelEvent $event
     */
    public function cancelThrowOrder(GarbageThrowOrderCancelEvent $event)
    {
        $data = $event->data;

        $recyclerId = $data['recycler_id'];
        $throwDate = $data['throw_date'];
        $timePriod = $data['time_period'];

        // 指定回收员在指定时间段的代仍单数-1
        $redis = Redis::connection('throw');
        $recyclerThrowCountKey = RedisKeyConst::THROW_RECYCLER_ORDER_COUNT_PREFIX . $recyclerId . ':' . $throwDate;
        $redis->hincrby($recyclerThrowCountKey, $timePriod, -1);
    }
}