<?php

namespace App\Services\GarbageRecycle;

use App\Dto\GarbageRecycleOrderDto;
use App\Dto\GarbageRecycleOrderItemsDto;
use App\Events\GarbageRecycleOrderCancelEvent;
use App\Events\GarbageRecycleOrderCreateEvent;
use App\Exceptions\RestfulException;
use App\Services\Common\ConfigService;
use App\Services\Village\VillageService;
use App\Supports\Constant\ConfigConst;
use App\Supports\Constant\GarbageRecycleConst;
use App\Supports\Constant\GarbageSiteConst;
use App\Supports\Constant\RedisKeyConst;
use App\Supports\Constant\VillageConst;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class GarbageRecycleOrderService
{
    /**
     * 查询指定小区指定日期的可回收时间段列表.
     *
     * @param string $recyclingDate
     * @param int $villageId
     *
     * @return mixed
     */
    public function getRecycleTimePeriodList($recyclingDate, $villageId)
    {
        // 判断用户选择的小区是否开启支持代仍.
        $villageInfo = app(VillageService::class)->getVillageDetail($villageId, ['*', 'recycler.*']);
        if (empty($villageInfo)) {
            throw new RestfulException('该小区信息不存在，请重新选择！');
        }
        if ($villageInfo['is_recycle'] != VillageConst::RECYCLE_IS_SUPPORTED) {
            throw new RestfulException('抱歉，该小区目前暂未开通回收服务，请耐心期待！');
        }
        $isThrow = $villageInfo['is_throw'];
        $recyclerId = $villageInfo['recycler']['id'];

        $throwTimePeriods = app(ConfigService::class)->getConfig(ConfigConst::THROW_GARBAGE_TIME);
        $recycleTimePeriods = app(ConfigService::class)->getConfig(ConfigConst::RECYCLE_GARBAGE_TIME);

        // 获取所有回收时间段
        if (!$isThrow) {
            // 如果该小区不支持代仍，直接取配置的回收时间段
            $allRecyclePeriod = $recycleTimePeriods;
        } else {
            // 否则，取代仍时间段与回收时间段的差集
            $allRecyclePeriod = array_filter($recycleTimePeriods, function ($timePeriod) use ($throwTimePeriods) {
                [$startTime1, $endTime1] = explode('-', $timePeriod);
                foreach ($throwTimePeriods as $overlapPeriod) {
                    [$startTime2, $endTime2] = explode('-', $overlapPeriod);
                    if (is_time_overlap($startTime1, $endTime1, $startTime2, $endTime2)) {
                        return false;
                    }
                }

                return true;
            });
        }

        // 判断各个回收时间段是否满约.
        $recycleNumPerTime = app(ConfigService::class)->getConfig(ConfigConst::RECYCLE_GARBAGE_NUM_PER_TIME);
        $redis = Redis::connection('recycle');
        return array_map(function ($period) use ($recyclerId, $recyclingDate, $recycleNumPerTime, $redis) {
            $recyclerOrderCount = $redis->hget(RedisKeyConst::RECYCLE_RECYCLER_ORDER_COUNT_PREFIX . $recyclerId . ':' . $recyclingDate, $period);
            return [
                'time_period' => $period,
                'is_full' => $recyclerOrderCount >= $recycleNumPerTime
            ];
        }, $allRecyclePeriod);
    }

    /**
     * 创建回收订单.
     *
     * @param int $userId
     * @param int $villageId
     * @param int $villageFloorId
     * @param string $address
     * @param string $recyclingDate
     * @param string $recyclingStartTime
     * @param string $recyclingEndTime
     * @param string $remark
     * @param array $recycleItems
     *              -> int garbage_category_id
     *              -> int garbage_type_id
     *              -> double price
     *              -> double pre_weight
     *
     * @return int 创建订单的编号
     *
     */
    public function createGarbageRecycleOrder($userId, $villageId, $villageFloorId, $address, $recyclingDate, $recyclingStartTime, $recyclingEndTime, $remark, $recycleItems)
    {
//        \DateTime::createFromFormat("H:i-H:i", "09:00-10:00");
        // 判断用户是否授权登录.
        if (empty($userId)) {
            throw new RestfulException('用户必须授权登录，请先授权登录！');
        }

        // 查询小区是否存在.
        $villageInfo = app(VillageService::class)->getVillageDetail($villageId, ['*', 'site.*', 'recycler.*']);
        if (empty($villageInfo)) {
            throw new RestfulException('该小区信息不存在，请重新选择！');
        }

        // 检查站点是否营业、是否支持回收.
        $siteId = $villageInfo['site_id'];
        $siteInfo = $villageInfo['site'];
        if (empty($siteInfo['is_work']) || $siteInfo['is_work'] != GarbageSiteConst::GARBAGE_SITE_STATUS_WORKING) {
            throw new RestfulException('该站点暂时没有营业！');
        }
        if (empty($siteInfo['is_recycle']) || $siteInfo['is_recycle'] != GarbageSiteConst::GARBAGE_SITE_RECYCLE_IS_SUPPORT) {
            throw new RestfulException('该站点暂不支持回收！');
        }

        // 检查代仍日期是否是站点支持的.
        $siteWorkTimeSlots = json_decode($siteInfo['work_time_slot'], true);
        if (!in_array(date('w', strtotime($recyclingDate)), $siteWorkTimeSlots)) {
            throw new RestfulException('抱歉，站点在您选择的回收日期不营业！');
        }

        // 判断用户选择的小区是否开启支持回收.
        if ($villageInfo['is_recycle'] != VillageConst::RECYCLE_IS_SUPPORTED) {
            throw new RestfulException('抱歉，该小区目前暂未开通回收服务，请耐心期待！');
        }

        // 判断有没有填写回收垃圾.
        if (empty($recycleItems)) {
            throw new RestfulException('必须添加回收垃圾！');
        }

        // 检验回收垃圾合法性.
        $garbageTotalAmount = 0.00;// 订单总额（预估，待回收员确认的时候再修改）
        foreach ($recycleItems as $recycleItem) {
            $garbageCategoryId = $recycleItem['garbage_category_id'];
            $garbageTypeId = $recycleItem['garbage_type_id'];
            $garbagePrice = $recycleItem['price'];
            $garbagePreWeight = $recycleItem['pre_weight'];
            $garbageTotalAmount += bcmul($garbagePrice, $garbagePreWeight);
            $garbageType = app(GarbageTypeService::class)->getGarbageTypeList(
                ['id' => $garbageTypeId, 'category_id' => $garbageCategoryId], ['id'], ['id' => 'asc'], 1, 1, false
            );
            if (empty($garbageType)) {
                throw new RestfulException('对应的垃圾分类不存在，请重新选择！');
            }
        }

        // 判断用户选择的日期和时间是否合理，且日期否为是今天、明天后天中的一天.
        if (strtotime($recyclingDate . ' ' . $recyclingStartTime) <= time()) {
            throw new RestfulException('回收开始时间必须晚于当前时间！');
        }
        if (strtotime($recyclingStartTime) >= strtotime($recyclingEndTime)) {
            throw new RestfulException('回收开始时间必须小于回收结束时间！');
        }
        if (strtotime($recyclingEndTime) - strtotime($recyclingStartTime) != 3600) {
            throw new RestfulException('回收时间区间跨度必须为1小时！');
        }
        $todayDate = date('Y-m-d');
        $tomorrowDate = date('Y-m-d', strtotime('+1 day'));
        $afterTomorrowDate = date('Y-m-d', strtotime('+2 day'));
        if (!in_array($recyclingDate, [$todayDate, $tomorrowDate, $afterTomorrowDate])) {
            throw new RestfulException('回收的日期必须为今天、明天或者后天中的一天，请重新选择！');
        }

        // 判断用户选择的时间是否在后台配置的范围内.
        $permissibleRecycleTimePeriods = app(ConfigService::class)->getConfig(ConfigConst::RECYCLE_GARBAGE_TIME);
        $recyclePeriod = $recyclingStartTime . '-' . $recyclingEndTime;
        if (!in_array($recyclePeriod, $permissibleRecycleTimePeriods)) {
            throw new RestfulException('该时间范围内不可回收垃圾，请重新选择！');
        }

        // 判断用户选择的时间段是否已经达到每小时回收单数极限值.
        $recyclerId = $villageInfo['recycler']['id'];

        // 每个小区只对应一个回收员，该回收员该小时区间时间段内的订单量不能超过12.
        $recycleNumPerTime = app(ConfigService::class)->getConfig(ConfigConst::RECYCLE_GARBAGE_NUM_PER_TIME);
        $redis = Redis::connection('recycle');
        $recyclerOrderCount = $redis->hget(RedisKeyConst::THROW_RECYCLER_ORDER_COUNT_PREFIX . $recyclerId . ':' . $recyclingDate, $recyclePeriod);
        if ($recyclerOrderCount >= $recycleNumPerTime) {
            throw new RestfulException('当前时间段的回收单数已满，请重新选择其他时间段！');
        }

        // 创建订单.
        $orderNo = generate_order_no($userId, 'R');
        $orderStatus = GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_RESERVED;
        $garbageRecycleOrderData = [
            'order_no' => $orderNo,
            'user_id' => $userId,
            'recycler_id' => $recyclerId,
            'site_id' => $siteId,
            'village_id' => $villageId,
            'village_floor_id' => $villageFloorId,
            'address' => $address,
            'status' => $orderStatus,
            'recycling_start_time' => $recyclingDate . ' ' . $recyclingStartTime,
            'recycling_end_time' => $recyclingDate . ' ' . $recyclingEndTime,
            'total_amount' => $garbageTotalAmount,
            'remark' => $remark
        ];
        $garbageRecycleOrderItemsData = array_map(function ($recycleItem) use ($orderNo) {
            return [
                'order_no' => $orderNo,
                'garbage_category_id' => $recycleItem['garbage_category_id'],
                'garbage_type_id' => $recycleItem['garbage_type_id'],
                'price' => $recycleItem['price'],
                'pre_weight' => $recycleItem['pre_weight']
            ];
        }, $recycleItems);


        // 创建回收订单.
        DB::transaction(function () use ($garbageRecycleOrderData, $garbageRecycleOrderItemsData) {
            // 生成回收主订单记录.
            app(GarbageRecycleOrderDto::class)->createRecycleOrder($garbageRecycleOrderData);

            // 生成回收订单明细记录.
            app(GarbageRecycleOrderItemsDto::class)->createRecycleOrderItems($garbageRecycleOrderItemsData);
        });

        // 订单创建成功，发起异步事件.
        event(new GarbageRecycleOrderCreateEvent([
            'order_no' => $orderNo,
            'recycler_id' => $recyclerId,
            'recycle_date' => $recyclingDate,
            'time_period' => $recyclePeriod
        ]));

        return $orderNo;
    }

    /**
     * 回收订单接单.
     *
     * @param string $orderNo
     *
     * @return bool
     *
     */
    public function receiveGarbageRecycleOrder($orderNo)
    {
        $orderInfo = $this->getGarbageRecycleOrderInfo(['order_no' => $orderNo]);
        if ($orderInfo['status'] != GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_RESERVED) {
            throw new RestfulException('该回收订单不属于已预约状态，不可接单！');
        }

        app(GarbageRecycleOrderDto::class)->updateRecycleOrder($orderNo, [
            'status' => GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_RECEIVED,
            'reveive_time' => date("Y-m-d H:i:s", time())
        ]);

        return true;
    }

    /**
     * 回收员确认回收订单（确认为待完成）.
     *
     * @param string $orderNo
     * @param array $orderItems
     *              -> garbage_type_id
     *              -> actual_weight
     *              -> price
     *
     * @return bool
     *
     */
    public function confirmRecycleOrderByRecycler($orderNo, $orderItems)
    {
        if (empty($orderItems)) {
            throw new RestfulException('回收订单明细不能为空！');
        }

        $orderInfo = $this->getGarbageRecycleOrderInfo(['order_no' => $orderNo]);
        if ($orderInfo['status'] != GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_RECEIVED) {
            throw new RestfulException('该回收订单不属于已接单状态，不可确认为待完成！');
        }

        DB::transaction(function () use ($orderNo, $orderItems) {
            $actualTotalAmount = 0.00;

            // 遍历填充订单明细.
            array_walk($orderItems, function ($orderItem) use ($orderNo, &$actualTotalAmount) {
                $garbageTypeId = $orderItem['garbage_type_id'];
                $actualWeight = $orderItem['actual_weight'];
                $price = $orderItem['price'];
                $actualAmount = bcmul($price, $actualWeight, 2);
                app(GarbageRecycleOrderItemsDto::class)->fillActualRecycleOrderItem($orderNo, $garbageTypeId, $actualWeight, $actualAmount);

                $actualTotalAmount += $actualAmount;
            });


            // 更新订单状态（更新为待完成）以及订单总金额.
            app(GarbageRecycleOrderDto::class)->updateRecycleOrder($orderNo, [
                'status' => GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_WAIT_FINISH,
                'total_amount' => $actualTotalAmount,
                'confirm_time' => date("Y-m-d H:i:s", time())
            ]);

        });

        return true;
    }

    /**
     * 用户确认回收订单（确认为已完成）.
     *
     * @param string $orderNo
     *
     * @return bool
     *
     */
    public function confirmRecycleOrderByUser($orderNo)
    {
        $orderInfo = $this->getGarbageRecycleOrderInfo(['order_no' => $orderNo]);
        if ($orderInfo['status'] != GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_WAIT_FINISH) {
            throw new RestfulException('该代仍订单不属于待完成状态，不可操作完成！');
        }

        // 完成订单.
        app(GarbageRecycleOrderDto::class)->updateRecycleOrder($orderNo, [
            'status' => GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_FINISHED,
            'finish_time' => date("Y-m-d H:i:s", time())
        ]);

        return true;
    }

    /**
     * 用户取消回收订单（用户预约取消）.
     *
     * @param string $orderNo
     *
     * @return bool
     *
     */
    public function cancelRecycleOrderByUserReserve($orderNo)
    {
        // 判断用户此时是否可以操作取消.
        $orderInfo = $this->getGarbageRecycleOrderInfo(['order_no' => $orderNo], ['*', 'recycler.*']);
        if ($orderInfo['status'] != GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_RESERVED) {
            throw new RestfulException('该回收订单已经被接单，不可取消！');
        }
        if (strtotime($orderInfo['recycling_start_time']) >= time()) {
            throw new RestfulException('当前已经到了预约回收时间，不可取消！');
        }

        // 取消订单（用户预约取消）.
        app(GarbageRecycleOrderDto::class)->updateRecycleOrder($orderNo, [
            'status' => GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_USER_RESERVE_CANCELED,
            'cancel_time' => date("Y-m-d H:i:s", time())
        ]);

        // 发起订单取消异步事件.
        $this->pushAsyncEventForCancelRecycleOrder($orderInfo);

        return true;
    }

    /**
     * 用户取消回收订单（用户确认取消）.
     *
     * @param string $orderNo
     *
     * @return bool
     *
     */
    public function cancelRecycleOrderByUserConfirm($orderNo)
    {
        // 判断用户此时是否可以操作取消.
        $orderInfo = $this->getGarbageRecycleOrderInfo(['order_no' => $orderNo], ['*', 'recycler.*']);
        if ($orderInfo['status'] != GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_WAIT_FINISH) {
            throw new RestfulException('该回收订单不属于待完成状态，不可操作取消！');
        }

        // 取消订单（用户确认取消）.
        app(GarbageRecycleOrderDto::class)->updateRecycleOrder($orderNo, [
            'status' => GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_USER_CONFIRM_CANCELED,
            'cancel_time' => date("Y-m-d H:i:s", time())
        ]);

        // 发起订单取消异步事件.
        $this->pushAsyncEventForCancelRecycleOrder($orderInfo);

        return true;
    }

    /**
     * 回收员取消回收订单.
     *
     * @param string $orderNo
     *
     * @return bool
     *
     */
    public function cancelRecycleOrderByRecycler($orderNo)
    {
        // 判断回收员此时是否可以操作取消.
        $orderInfo = $this->getGarbageRecycleOrderInfo(['order_no' => $orderNo, ['*', 'recycler.*']]);
        if ($orderInfo['status'] != GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_RECEIVED) {
            throw new RestfulException('该回收订单不属于已接单状态，您当前不可操作取消！');
        }
        if ($orderInfo['recycling_end_time'] < date("Y-m-d H:i:s", time())) {
            throw new RestfulException('当前时间已经过了预约回收时间，您当前不可操作取消！');
        }

        // 取消订单（回收员取消）.
        app(GarbageRecycleOrderDto::class)->updateRecycleOrder($orderNo, [
            'status' => GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_RECYCLER_CANCELED,
            'cancel_time' => date("Y-m-d H:i:s", time())
        ]);

        // 发起订单取消异步事件.
        $this->pushAsyncEventForCancelRecycleOrder($orderInfo);

        return true;
    }

    /**
     * 系统取消回收订单.
     *
     * @param string $orderNo
     *
     * @return bool
     *
     */
    public function cancelRecycleOrderBySystem($orderNo)
    {
        $orderInfo = $this->getGarbageRecycleOrderInfo(['order_no' => $orderNo]);
        if ($orderInfo['status'] != GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_RECEIVED) {
            throw new RestfulException('该代仍订单不属于未接单状态，不可系统取消！');
        }
        if ($orderInfo['recycling_end_time'] >= date("Y-m-d H:i:s", time())) {
            throw new RestfulException('回收代仍时间还未截止，不可系统取消！');
        }

        // 取消订单（系统取消）.
        app(GarbageRecycleOrderDto::class)->updateThrowOrder($orderNo, [
            'status' => GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_SYSTEM_CANCELED,
            'cancel_time' => date("Y-m-d H:i:s", time())
        ]);

        // 发起订单取消异步事件.
        $this->pushAsyncEventForCancelRecycleOrder($orderInfo);

        return true;
    }

    /**
     * 通用查询订单列表.
     *
     * @param array $where
     * @param array $select
     * @param array $orderBy
     * @param int $page $page=0时，不分页
     * @param int $pageSize
     *
     * @return mixed
     *
     */
    public function getGarbageRecycleOrderList($where, $select, $orderBy, $page, $pageSize)
    {
        $garbageRecycleOrderList = app(GarbageRecycleOrderDto::class)->getRecycleOrderList($where, $select, $orderBy, $page, $pageSize);

        return $garbageRecycleOrderList;
    }

    /**
     * 通用查询单个订单.
     *
     * @param array $where
     * @param array $select
     *
     * @return mixed
     *
     */
    public function getGarbageRecycleOrderInfo($where, $select = ['*'])
    {
        $garbageRecycleOrderInfo = app(GarbageRecycleOrderDto::class)->getRecycleOrderInfo($where, $select);

        return $garbageRecycleOrderInfo;
    }

    /**
     * 发起回收订单取消异步事件.
     *
     * @param array $orderInfo
     *
     * @return mixed
     *
     */
    private function pushAsyncEventForCancelRecycleOrder($orderInfo)
    {
        $recyclerId = $orderInfo['recycler']['id'];
        $recyclingDate = date('Y-m-d', strtotime($orderInfo['recycling_start_time']));
        $recyclePeriod = date('H:i', strtotime($orderInfo['recycling_start_time'])) . '-' . date('H:i', strtotime($orderInfo['recycling_end_time']));

        event(new GarbageRecycleOrderCancelEvent([
            'order_no' => $orderInfo['order_no'],
            'recycler_id' => $recyclerId,
            'recycle_date' => $recyclingDate,
            'time_period' => $recyclePeriod
        ]));
    }
}