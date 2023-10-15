<?php

namespace App\Services\GarbageThrow;

use App\Dto\GarbageThrowOrderDto;
use App\Events\GarbageThrowOrderCancelEvent;
use App\Events\GarbageThrowOrderCreateEvent;
use App\Exceptions\RestfulException;
use App\Services\Common\ConfigService;
use App\Services\Coupon\ThrowCouponService;
use App\Services\Village\VillageService;
use App\Supports\Constant\ConfigConst;
use App\Supports\Constant\CouponConst;
use App\Supports\Constant\GarbageSiteConst;
use App\Supports\Constant\GarbageThrowConst;
use App\Supports\Constant\RedisKeyConst;
use App\Supports\Constant\VillageConst;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class GarbageThrowOrderService
{
    /**
     * 查询指定小区指定日期的可代仍时间段列表.
     *
     * @param string $throwingDate
     * @param int $villageId
     *
     * @return mixed
     */
    public function getThrowableTimePeriodList($throwingDate, $villageId)
    {
        // 判断用户选择的小区是否开启支持代仍.
        $villageInfo = app(VillageService::class)->getVillageDetail($villageId, ['*', 'recycler.*']);
        if (empty($villageInfo)) {
            throw new RestfulException('该小区信息不存在，请重新选择！');
        }
        if ($villageInfo['is_throw'] != VillageConst::THROW_IS_SUPPORTED) {
            throw new RestfulException('抱歉，该小区目前暂未开通代仍服务，请耐心期待！');
        }
        $isRecycle = $villageInfo['is_recycle'];
        $recyclerId = $villageInfo['recycler']['id'];

        $throwTimePeriods = app(ConfigService::class)->getConfig(ConfigConst::THROW_GARBAGE_TIME);
        $recycleTimePeriods = app(ConfigService::class)->getConfig(ConfigConst::RECYCLE_GARBAGE_TIME);

        // 获取所有代仍时间段
        if (!$isRecycle) {
            // 如果该小区不支持回收，直接取配置的代仍时间段
            $allThrowPeriod = $throwTimePeriods;
        } else {
            // 否则，取代仍时间段与回收时间段的差集
            $allThrowPeriod = array_filter($throwTimePeriods, function($timePeriod) use($recycleTimePeriods) {
                [$startTime1, $endTime1] = explode('-', $timePeriod);
                foreach ($recycleTimePeriods as $overlapPeriod) {
                    [$startTime2, $endTime2] = explode('-', $overlapPeriod);
                    if (is_time_overlap($startTime1, $endTime1, $startTime2, $endTime2)) {
                        return false;
                    }
                }

                return true;
            });
        }

        // 判断各个代仍时间段是否满约
        $throwNumPerTime = app(ConfigService::class)->getConfig(ConfigConst::THROW_GARBAGE_NUM_PER_TIME);
        $redis = Redis::connection('throw');
        return array_map(function ($period) use ($recyclerId, $throwingDate, $throwNumPerTime, $redis) {
            $recyclerOrderCount = $redis->hget(RedisKeyConst::THROW_RECYCLER_ORDER_COUNT_PREFIX . $recyclerId . ':' . $throwingDate, $period);
            return [
                'time_period' => $period,
                'is_full' => $recyclerOrderCount >= $throwNumPerTime
            ];
        }, $allThrowPeriod);
    }

    /**
     * 创建代仍订单.
     *
     * @param int $userId
     * @param int $throwType
     * @param int $villageId
     * @param int $villageFloorId
     * @param string $address
     * @param string $throwingDate
     * @param string $throwingStartTime
     * @param string $throwingEndTime
     * @param string $remark
     * @param array $couponIds
     *
     * @return string
     *
     */
    public function createGarbageThrowOrder($userId, $throwType, $villageId, $villageFloorId, $address, $throwingDate, $throwingStartTime, $throwingEndTime, $remark, $couponIds)
    {
        // 判断用户是否授权登录.
        if (empty($userId)) {
            throw new RestfulException('用户必须授权登录，请先授权登录！');
        }

        // 判断代仍类型是否合法.
        if (!in_array($throwType, GarbageThrowConst::GARBAGE_THROW_TYPES)) {
            throw new RestfulException('代仍垃圾类型不合法！');
        }

        // 判断代仍券.
        if (empty($couponIds)) {
            throw new RestfulException('必须选择代仍券！');
        }
        $usedCouponNumber = sizeof(array_unique($couponIds));

        $couponList = app(ThrowCouponService::class)->getCouponListByIds($couponIds, CouponConst::THROW_WAIT_STATUS);
        if (sizeof($couponList) != $usedCouponNumber) {
            throw new RestfulException('存在无效的代仍券！');
        }

        // 查询小区是否存在.
        $villageInfo = app(VillageService::class)->getVillageDetail($villageId, ["*", "recycler.*", "site.*"]);
        if (empty($villageInfo)) {
            throw new RestfulException('该小区信息不存在，请重新选择！');
        }

        // 检查站点是否营业、是否支持代仍.
        $siteId = $villageInfo['site_id'];
        $siteInfo = $villageInfo['site'];
        if (empty($siteInfo['is_work']) || $siteInfo['is_work'] != GarbageSiteConst::GARBAGE_SITE_STATUS_WORKING) {
            throw new RestfulException('该站点暂时没有营业！');
        }
        if (empty($siteInfo['is_throw']) || $siteInfo['is_throw'] != GarbageSiteConst::GARBAGE_SITE_THROW_IS_SUPPORT) {
            throw new RestfulException('该站点暂不支持代仍！');
        }

        // 检查代仍日期是否是站点支持的.
        $siteWorkTimeSlots = json_decode($siteInfo['work_time_slot'], true);
        if (!in_array(date('w', strtotime($throwingDate)), $siteWorkTimeSlots)) {
            throw new RestfulException('抱歉，站点在您选择的代仍日期不营业！');
        }

        // 判断用户选择的小区是否开启支持代仍.
        if ($villageInfo['is_throw'] != VillageConst::THROW_IS_SUPPORTED) {
            throw new RestfulException('抱歉，该小区目前暂未开通代仍服务，请耐心期待！');
        }

        // 判断用户选择的日期和时间是否合理，且日期否为是今天、明天后天中的一天.
        if (strtotime($throwingDate . ' ' . $throwingStartTime) <= time()) {
            throw new RestfulException('代仍开始时间必须晚于当前时间！');
        }
        if (strtotime($throwingStartTime) >= strtotime($throwingEndTime)) {
            throw new RestfulException('代仍开始时间必须小于代仍结束时间！');
        }
        if (strtotime($throwingEndTime) - strtotime($throwingStartTime) != 3600) {
            throw new RestfulException('代仍时间区间跨度必须为1小时！');
        }
        $todayDate = date('Y-m-d');
        $tomorrowDate = date('Y-m-d', strtotime('+1 day'));
        $afterTomorrowDate = date('Y-m-d', strtotime('+2 day'));
        if (!in_array($throwingDate, [$todayDate, $tomorrowDate, $afterTomorrowDate])) {
            throw new RestfulException('代仍的日期必须为今天、明天或者后天中的一天，请重新选择！');
        }

        // 判断用户选择的时间是否在后台配置的范围内.
        $permissibleThrowTimePeriods = app(ConfigService::class)->getConfig(ConfigConst::THROW_GARBAGE_TIME);
        $throwPeriod = $throwingStartTime . '-' . $throwingEndTime;
        if (!in_array($throwPeriod, $permissibleThrowTimePeriods)) {
            throw new RestfulException('该时间范围内不可代仍垃圾，请重新选择！');
        }

        // 判断用户选择的时间段是否已经达到每小时代仍单数极限值.
        $recyclerId = $villageInfo['recycler']['id'];

        // 每个小区只对应一个回收员，该回收员该小时区间时间段内的订单量不能超过20.
        $throwNumPerTime = app(ConfigService::class)->getConfig(ConfigConst::THROW_GARBAGE_NUM_PER_TIME);
        $redis = Redis::connection('throw');
        $recyclerOrderCount = $redis->hget(RedisKeyConst::THROW_RECYCLER_ORDER_COUNT_PREFIX . $recyclerId . ':' . $throwingDate, $throwPeriod);
        if ($recyclerOrderCount >= $throwNumPerTime) {
            throw new RestfulException('当前时间段的代仍单数已满，请重新选择其他时间段！');
        }

        // 创建订单.
        $orderNo = generate_order_no($userId, 'T');
        $orderStatus = GarbageThrowConst::GARBAGE_THROW_ORDER_STATUS_RESERVED;
        $garbageThrowOrderData = [
            'order_no' => $orderNo,
            'user_id' => $userId,
            'recycler_id' => $recyclerId,
            'throw_type' => $throwType,
            'site_id' => $siteId,
            'village_id' => $villageId,
            'village_floor_id' => $villageFloorId,
            'address' => $address,
            'status' => $orderStatus,
            'throwing_start_time' => $throwingDate . ' ' . $throwingStartTime,
            'throwing_end_time' => $throwingDate . ' ' . $throwingEndTime,
            'remark' => $remark,
            'used_coupon_number' => $usedCouponNumber,
            'max_throw_number' => GarbageThrowConst::GARBAGE_THROW_NUMBERS_PER_COUPON * $usedCouponNumber
        ];

        DB::transaction(function () use ($garbageThrowOrderData, $couponIds) {
            // 下单.
            app(GarbageThrowOrderDto::class)->createThrowOrder($garbageThrowOrderData);

            // 用券.
            foreach ($couponIds as $couponId) {
                app(ThrowCouponService::class)->UseCoupon($couponId);
            }
        });


        // 订单创建成功，发起异步事件.
        event(new GarbageThrowOrderCreateEvent([
            'order_no' => $orderNo,
            'recycler_id' => $recyclerId,
            'throw_date' => $throwingDate,
            'time_period' => $throwPeriod
        ]));

        return $orderNo;
    }

    /**
     * 接单代仍订单.
     *
     * @param string $orderNo
     *
     * @return bool
     *
     */
    public function receiveGarbageThrowOrder($orderNo)
    {
        $orderInfo = $this->getGarbageThrowOrderInfo(['order_no' => $orderNo]);
        if (empty($orderInfo)) {
            throw new RestfulException('该代仍订单不存在！');
        }
        if ($orderInfo['status'] != GarbageThrowConst::GARBAGE_THROW_ORDER_STATUS_RESERVED) {
            throw new RestfulException('该代仍订单不属于已预约状态，不可接单！');
        }

        app(GarbageThrowOrderDto::class)->updateThrowOrder($orderNo, [
            'status' => GarbageThrowConst::GARBAGE_THROW_ORDER_STATUS_RECEIVED,
            'reveive_time' => date("Y-m-d H:i:s", time())
        ]);

        return true;
    }

    /**
     * 完成代仍订单.
     *
     * @param string $orderNo
     *
     * @return bool
     *
     */
    public function finishGarbageThrowOrder($orderNo)
    {
        $orderInfo = $this->getGarbageThrowOrderInfo(['order_no' => $orderNo]);
        if (empty($orderInfo)) {
            throw new RestfulException('该代仍订单不存在！');
        }
        if ($orderInfo['status'] != GarbageThrowConst::GARBAGE_THROW_ORDER_STATUS_RECEIVED) {
            throw new RestfulException('该代仍订单不属于已接单状态，不可接单！');
        }

        app(GarbageThrowOrderDto::class)->updateThrowOrder($orderNo, [
            'status' => GarbageThrowConst::GARBAGE_THROW_ORDER_STATUS_FINISHED,
            'finish_time' => date("Y-m-d H:i:s", time())
        ]);

        return true;
    }

    /**
     * 用户取消代仍订单.
     *
     * @param string $orderNo
     *
     * @return bool
     *
     */
    public function cancelGarbageThrowOrderByUser($orderNo)
    {
        // 判断用户此时是否可以操作取消.
        $orderInfo = $this->getGarbageThrowOrderInfo(['order_no' => $orderNo], ['*', 'recycler.*']);
        if ($orderInfo['status'] != GarbageThrowConst::GARBAGE_THROW_ORDER_STATUS_RESERVED) {
            throw new RestfulException('该代仍订单已经不属于已预约状态，不可取消！');
        }
        if ($orderInfo['throwing_start_time'] <= date("Y-m-d H:i:s", time())) {
            throw new RestfulException('当前已经到了预约代仍时间，不可取消！');
        }

        // 取消订单（用户取消）.
        app(GarbageThrowOrderDto::class)->updateThrowOrder($orderNo, [
            'status' => GarbageThrowConst::GARBAGE_THROW_ORDER_STATUS_USER_CANCELED,
            'cancel_time' => date("Y-m-d H:i:s", time())
        ]);

        // 发起订单取消异步事件.
        $this->pushAsyncEventForCancelThrowOrder($orderInfo);

        return true;
    }

    /**
     * 回收员取消代仍订单.
     *
     * @param string $orderNo
     *
     * @return bool
     *
     */
    public function cancelGarbageThrowOrderByRecycler($orderNo)
    {
        // 判断回收员此时是否可以操作取消.
        $orderInfo = $this->getGarbageThrowOrderInfo(['order_no' => $orderNo, ['*', 'recycler.*']]);
        if ($orderInfo['status'] != GarbageThrowConst::GARBAGE_THROW_ORDER_STATUS_RECEIVED) {
            throw new RestfulException('该代仍订单不属于已接单状态，您当前不可操作取消！');
        }
        if ($orderInfo['throwing_end_time'] < date("Y-m-d H:i:s", time())) {
            throw new RestfulException('当前时间已经过了预约代仍时间，您当前不可操作取消！');
        }

        // 取消订单（回收员取消）.
        app(GarbageThrowOrderDto::class)->updateThrowOrder($orderNo, [
            'status' => GarbageThrowConst::GARBAGE_THROW_ORDER_STATUS_RECYCLER_CANCELED,
            'cancel_time' => date("Y-m-d H:i:s", time())
        ]);

        // 发起订单取消异步事件.
        $this->pushAsyncEventForCancelThrowOrder($orderInfo);

        return true;
    }

    /**
     * 系统取消代仍订单.
     *
     * @param string $orderNo
     *
     * @return bool
     *
     */
    public function cancelGarbageThrowOrderBySystem($orderNo)
    {
        $orderInfo = $this->getGarbageThrowOrderInfo(['order_no' => $orderNo]);
        if ($orderInfo['status'] != GarbageThrowConst::GARBAGE_THROW_ORDER_STATUS_RECEIVED) {
            throw new RestfulException('该代仍订单不属于未接单状态，不可系统取消！');
        }
        if ($orderInfo['throwing_end_time'] >= date("Y-m-d H:i:s", time())) {
            throw new RestfulException('预约代仍时间还未截止，不可系统取消！');
        }

        // 取消订单（系统取消）.
        app(GarbageThrowOrderDto::class)->updateThrowOrder($orderNo, [
            'status' => GarbageThrowConst::GARBAGE_THROW_ORDER_STATUS_SYSTEM_CANCELED,
            'cancel_time' => date("Y-m-d H:i:s", time())
        ]);

        // 发起订单取消异步事件.
        $this->pushAsyncEventForCancelThrowOrder($orderInfo);

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
    public function getGarbageThrowOrderList($where, $select, $orderBy, $page, $pageSize)
    {
        $garbageThrowOrderList = app(GarbageThrowOrderDto::class)->getThrowOrderList($where, $select, $orderBy, $page, $pageSize);

        return $garbageThrowOrderList;
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
    public function getGarbageThrowOrderInfo($where, $select = ['*'])
    {
        $garbageThrowOrderInfo = app(GarbageThrowOrderDto::class)->getThrowOrderInfo($where, $select);

        return $garbageThrowOrderInfo;
    }

    /**
     * 发起代仍订单取消异步事件.
     *
     * @param array $orderInfo
     *
     * @return mixed
     */
    private function pushAsyncEventForCancelThrowOrder($orderInfo)
    {
        $recyclerId = $orderInfo['recycler']['id'];

        if (!empty($recyclerId)) {
            $throwingDate = date('Y-m-d', strtotime($orderInfo['throwing_start_time']));
            $throwPeriod = date('H:i', strtotime($orderInfo['throwing_start_time'])) . '-' . date('H:i', strtotime($orderInfo['throwing_end_time']));

            event(new GarbageThrowOrderCancelEvent([
                'order_no' => $orderInfo['order_no'],
                'recycler_id' => $recyclerId,
                'throw_date' => $throwingDate,
                'time_period' => $throwPeriod
            ]));
        }
    }

}