<?php

namespace App\Services\GarbageRecycle;

use App\Events\GarbageRecycleOrderCancelEvent;
use App\Events\GarbageRecycleOrderCreateEvent;
use App\Events\GarbageRecycleOrderFinishEvent;
use App\Exceptions\RestfulException;
use App\Models\GarbageOrderDetailModel;
use App\Models\GarbageOrderModel;
use App\Services\Activity\CouponService;
use App\Services\Activity\InvitationService;
use App\Services\Common\ConfigService;
use App\Services\JifenShop\JifenOrderService;
use App\Services\User\AddressService;
use App\Services\User\AssertService;
use App\Services\User\UserService;
use App\Supports\Constant\ActivityConst;
use App\Supports\Constant\GarbageRecycleConst;
use App\Supports\Constant\RedisKeyConst;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Redis;

class GarbageRecycleOrderService
{
    /**
     * 查询可回收时间段列表.
     *
     * @return mixed
     */
    public function getRecycleTimePeriodList()
    {
        // 查询未来一周内所有支持的回收时间段.
        $allRecyclePeriod = app(ConfigService::class)->getConfig(GarbageRecycleConst::GARBAGE_RECYCLE_APPOINT_PERIOD);

        // 过滤已经满约的回收时间段，返回可预约的时间段.
        // 预约已满的时间段，显示为已约满.
        $todayDate = date('Y-m-d');
        $recycleOrderNumPerTime = app(ConfigService::class)->getConfig(GarbageRecycleConst::GARBAGE_RECYCLE_MAX_ORDERS_PER_PERIOD);
        $redis = Redis::connection('recycle');
        $allRecycleDates = [
            $todayDate,
            date('Y-m-d', strtotime('+1 day')),
            date('Y-m-d', strtotime('+2 day')),
            date('Y-m-d', strtotime('+3 day')),
            date('Y-m-d', strtotime('+4 day')),
            date('Y-m-d', strtotime('+5 day')),
            date('Y-m-d', strtotime('+6 day'))
        ];

        // 计算今天可选的时间段.
        $todayRecyclePeriod = [];
        foreach ($allRecyclePeriod as $period) {
            if (strtotime($period['start_time']) - time() > 7200) {
                $todayRecyclePeriod[] = $period;
            }
        }

        $recycleTimePeriodList = [];
        foreach ($allRecycleDates as $recyclingDate) {
            if ($recyclingDate == $todayDate) {
                if (empty($todayRecyclePeriod)) {
                    continue;
                }

                $recycleTimePeriodList[$recyclingDate] = array_map(function ($period) use ($recyclingDate, $recycleOrderNumPerTime, $redis) {
                    $recyclePeriod = date('H:i', strtotime($period['start_time'])) . '-' . date('H:i', strtotime($period['end_time']));
                    $recyclerOrderCount = $redis->hget(RedisKeyConst::RECYCLE_RECYCLER_ORDER_COUNT_PREFIX . ':' . $recyclingDate, $recyclePeriod);
                    return [
                        'time_period' => $recyclePeriod,
                        'is_full' => $recyclerOrderCount >= $recycleOrderNumPerTime
                    ];
                }, $todayRecyclePeriod);
            } else {
                $recycleTimePeriodList[$recyclingDate] = array_map(function ($period) use ($recyclingDate, $recycleOrderNumPerTime, $redis) {
                    $recyclePeriod = date('H:i', strtotime($period['start_time'])) . '-' . date('H:i', strtotime($period['end_time']));
                    $recyclerOrderCount = $redis->hget(RedisKeyConst::RECYCLE_RECYCLER_ORDER_COUNT_PREFIX . ':' . $recyclingDate, $recyclePeriod);
                    return [
                        'time_period' => $recyclePeriod,
                        'is_full' => $recyclerOrderCount >= $recycleOrderNumPerTime
                    ];
                }, $allRecyclePeriod);
            }
        }

        return $recycleTimePeriodList;
    }

    /**
     * 创建回收订单.
     *
     * @param int $userId
     * @param int $addressId
     * @param string $recyclingDate
     * @param string $recyclingStartTime
     * @param string $recyclingEndTime
     * @param string $predictWeight
     * @param array $promotion
     *              -> bean_num: 绿豆使用数量
     *              -> voucher_coupon_id: 代金券id
     *              -> phone_coupon_id: 话费券id
     *              -> expand_coupon_id: 膨胀券id
     *              -> exchange_order_no: 物品兑换订单号
     *              -> exchange_coupon_id: 兑换券id
     *
     * @return string 创建订单的编号
     *
     */
    public function createGarbageRecycleOrder($userId, $addressId, $recyclingDate, $recyclingStartTime, $recyclingEndTime, $predictWeight, $promotion)
    {
        // 判断用户是否授权登录.
        if (empty($userId)) {
            throw new RestfulException('用户必须授权登录，请先授权登录！');
        }

        // 查询地址是否存在.
        $addressInfo = app(AddressService::class)->getAddressDetail($addressId);
        if (empty($addressInfo)) {
            throw new RestfulException('该地址信息不存在，请重新选择！');
        }

        // 回收时间必须在当前时间之后.
        if (strtotime($recyclingDate . ' ' . $recyclingStartTime) <= time()) {
            throw new RestfulException('回收时间必须在当前时间之后！');
        }

        // 回收开始时间必须小于回收结束时间.
        if (strtotime($recyclingStartTime) >= strtotime($recyclingEndTime)) {
            throw new RestfulException('回收开始时间必须小于回收结束时间！');
        }

        // 只能预约7天内的回收单.
        $todayDate = date('Y-m-d', time());
        $sevenAfterDate = date('Y-m-d', strtotime('+7 day'));
        if ($recyclingDate < $todayDate || $recyclingDate > $sevenAfterDate) {
            throw new RestfulException('回收的日期必须为今天开始的7天内，请重新选择！');
        }

        // 判断用户选择的时间段是否已经达到每小时回收单数极限值.
        $recyclePeriod = $recyclingStartTime . '-' . $recyclingEndTime;
        $recycleOrderNumPerTime = app(ConfigService::class)->getConfig(GarbageRecycleConst::GARBAGE_RECYCLE_MAX_ORDERS_PER_PERIOD);
        $redis = Redis::connection('recycle');
        $recyclerOrderCount = $redis->hget(RedisKeyConst::THROW_RECYCLER_ORDER_COUNT_PREFIX . ':' . $recyclingDate, $recyclePeriod);
        if ($recyclerOrderCount >= $recycleOrderNumPerTime) {
            throw new RestfulException('当前时间段的回收单数已满，请重新选择其他时间段！');
        }

        // 判断当前用户进行中的订单只能下一个单.
        $checkOngoingOrderCount = GarbageOrderModel::query()->where('user_id', $userId)->whereIn('status', GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_ONGOING)->count(['id']);
        if ($checkOngoingOrderCount > 0) {
            throw new RestfulException('您已经预约上门回收，不可重复预约！');
        }


        // 创建订单.
        $orderNo = generate_order_no('R');
        $orderStatus = GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_RESERVED;
        $garbageTotalAmount = 0.00;// 订单总额（待回收员确认的时候再修改）
        $garbageRecycleOrderData = [
            'order_no' => $orderNo,
            'user_id' => $userId,
            'mobile' => $addressInfo['mobile'],
            'address_id' => $addressId,
            'status' => $orderStatus,
            'predict_weight' => $predictWeight,
            'appoint_start_time' => $recyclingDate . ' ' . $recyclingStartTime,
            'appoint_end_time' => $recyclingDate . ' ' . $recyclingEndTime,
            'total_amount' => $garbageTotalAmount,
            'promotion_info' => json_encode($promotion) // 下单存优惠券信息以及绿豆信息，绿豆只在订单完成的时候扣减，优惠券也在订单完成时使用
        ];

        // 创建回收订单.
        GarbageOrderModel::query()->insert($garbageRecycleOrderData);

        // 订单创建成功，发起异步事件.
        event(new GarbageRecycleOrderCreateEvent([
            'order_no' => $orderNo,
            'recycle_date' => $recyclingDate,
            'recycle_period' => $recyclePeriod
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

        GarbageOrderModel::query()->where('order_no', $orderNo)->update([
            'status' => GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_RECEIVED,
            'receive_time' => date("Y-m-d H:i:s", time())
        ]);

        return true;
    }


    /**
     * 开始回收（回收员上门）.
     *
     * @param $orderNo string 订单号
     * @return bool
     */
    public function startGarbageRecycleOrder($orderNo)
    {
        $orderInfo = $this->getGarbageRecycleOrderInfo(['order_no' => $orderNo]);
        if ($orderInfo['status'] != GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_RECEIVED) {
            throw new RestfulException('该回收订单不属于已接单状态，不可开始回收！');
        }

        GarbageOrderModel::query()->where('order_no', $orderNo)->update([
            'status' => GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_RECYCLING,
            'recycle_time' => date("Y-m-d H:i:s", time())
        ]);

        return true;
    }

    /**
     * 回收员设置回收订单明细（添加分类）.
     *
     * @param $orderNo
     * @param $orderDetails
     *          -> [i].garbage_type_id 二级分类id
     *          -> [i].actual_weight 实际重量
     *          -> [i].recycle_price 回收单价
     * @return bool
     */
    public function setGarbageRecycleOrderDetails($orderNo, $orderDetails)
    {
        // 检查订单状态.
        $orderInfo = $this->getGarbageRecycleOrderInfo(['order_no' => $orderNo]);
        if ($orderInfo['status'] != GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_RECYCLING) {
            throw new RestfulException('该回收订单不属于回收中状态，不可添加分类！');
        }
        $orderDetailsData = [];
        foreach ($orderDetails as $orderDetail) {
            $orderDetailsData[] = [
                'garbage_order_no' => $orderNo,
                'garbage_type_id' => $orderDetail['garbage_type_id'],
                'actual_weight' => $orderDetail['actual_weight'],
                'recycle_price' => $orderDetail['recycle_price'],
                'recycle_amount' => bcmul($orderDetail['actual_weight'], $orderDetail['recycle_price'], 2)
            ];
        }

        if (!empty($orderDetailsData)) {
            GarbageOrderDetailModel::query()->insert($orderDetailsData);
        }

        return true;
    }

    /**
     * 回收员完成回收订单.
     *
     * @param string $orderNo 订单号
     * @param double $recycleAmount 订单实际回收金额
     *
     * @return bool
     *
     * @throws \Throwable
     */
    public function finishGarbageRecycleOrder($orderNo, $recycleAmount)
    {
        // 检查订单状态
        $orderInfo = $this->getGarbageRecycleOrderInfo(['order_no' => $orderNo]);
        if ($orderInfo['status'] != GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_RECYCLING) {
            throw new RestfulException('该回收订单不属于回收中状态，不可完成！');
        }

        // 计算订单促销信息
        $totalAmount = $recycleAmount;
        $userId = $orderInfo['user_id'];
        $promotionInfo = $orderInfo['promotion_info'];

        // 订单绿豆逻辑
        $beanAmount = 0;
        if (!empty($promotionInfo['bean_num'])) {
            // 扣减绿豆
            app(InvitationService::class)->costBean($userId, $promotionInfo['bean_num']);
            app(InvitationService::class)->consumeBean($orderInfo);

            // 增加交易额
            $beanAmount = $promotionInfo['bean_num'] * ActivityConst::BEAN_WITHDRAW_RATIO;
            $totalAmount = bcadd($totalAmount, $beanAmount, 2);
        }

        // 订单代金券逻辑
        $voucherCouponAmount = 0;
        if (!empty($promotionInfo['voucher_coupon_id'])) {
            // 使用代金券
            app(CouponService::class)->useCoupon($userId, $promotionInfo['voucher_coupon_id'], $orderNo);
            // 增加交易额
            $voucherCouponInfo = app(CouponService::class)->getCouponDetail($promotionInfo['voucher_coupon_id']);
            $voucherCouponAmount = $voucherCouponInfo['amount'];
            $totalAmount = bcadd($totalAmount, $voucherCouponAmount, 2);
        }

        // 订单膨胀券逻辑
        $expandCouponAmount = 0;
        if (!empty($promotionInfo['expand_coupon_id'])) {
            // 使用膨胀券
            app(CouponService::class)->useCoupon($userId, $promotionInfo['expand_coupon_id'], $orderNo);

            // 计算膨胀金额
            $expandCouponInfo = app(CouponService::class)->getCouponDetail($promotionInfo['expand_coupon_id']);
            $expandTimes = $this->getExpandTimes($promotionInfo['expand_coupon_id']);
            $expandCouponAmount = $recycleAmount * $expandTimes;
            if ($recycleAmount > $expandCouponInfo['amount']) {
                $expandCouponAmount = $expandCouponInfo['amount'];
            }

            // 增加交易金额
            $totalAmount = bcadd($totalAmount, $expandCouponAmount, 2);
        }

        // 注意：话费券和物品兑换券，需要在订单完成的时候，回收员当场给与兑换.
        $phoneCouponAmount = 0;
        if (!empty($promotionInfo['phone_coupon_id'])) {
            // 增加交易金额
            $phoneCouponAmount = $this->getChargeAmount($promotionInfo['phone_coupon_id']);
            $totalAmount = bcadd($totalAmount, $phoneCouponAmount, 2);
            app(CouponService::class)->useCoupon($userId, $promotionInfo['phone_coupon_id'], $orderNo);
        }
        if (!empty($promotionInfo['exchange_order_no']) && !empty($promotionInfo['exchange_coupon_id'])) {
            // 更新兑换订单表的回收订单号
            app(JifenOrderService::class)->exchangeJiFenOrder($promotionInfo['exchange_order_no'], $orderNo);

            // 使用卡券.
            app(CouponService::class)->useCoupon($userId, $promotionInfo['exchange_coupon_id'], $orderNo);
        }

        // 修改订单完成数据.
        GarbageOrderModel::query()->where('order_no', $orderNo)->update([
            'status' => GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_FINISHED,
            'total_amount' => $totalAmount,
            'bean_amount' => $beanAmount,
            'voucher_coupon_amount' => $voucherCouponAmount,
            'expand_coupon_amount' => $expandCouponAmount,
            'phone_coupon_amount' => $phoneCouponAmount,
            'finish_time' => date("Y-m-d H:i:s", time())
        ]);

        // 发起订单完成异步事件.
        event(new GarbageRecycleOrderFinishEvent([
            'order_no' => $orderNo,
            'user_id' => $userId,
            'recycle_amount' => $recycleAmount,
            'total_amount' => $totalAmount
        ]));

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
    public function cancelRecycleOrderByUser($orderNo)
    {
        // 判断用户此时是否可以操作取消（只有待接单、已接单可以取消）.
        $orderInfo = $this->getGarbageRecycleOrderInfo(['order_no' => $orderNo], ['*']);
        if ($orderInfo['status'] != GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_RESERVED && $orderInfo['status'] != GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_RECEIVED) {
            throw new RestfulException('该回收订单正在回收中，不可取消！');
        }
        if (strtotime($orderInfo['appoint_start_time']) <= time()) {
            throw new RestfulException('当前已经到了预约回收时间，不可取消！');
        }

        // 取消订单（用户预约取消）.
        GarbageOrderModel::query()->where(['order_no' => $orderInfo])->update([
            'status' => GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_USER_CANCELED,
            'cancel_time' => date("Y-m-d H:i:s", time())
        ]);

        // 对于已接单的订单，取消扣减用户信用分.

        // 发起订单取消异步事件.
        $this->pushAsyncEventForCancelRecycleOrder($orderInfo);

        return true;
    }

    /**
     * 回收员取消回收订单（回收员主动取消）.
     *
     * @param string $orderNo
     *
     * @return bool
     *
     */
    public function cancelRecycleOrderByRecycler($orderNo)
    {
        // 判断用户此时是否可以操作取消.
        $orderInfo = $this->getGarbageRecycleOrderInfo(['order_no' => $orderNo], ['*']);
        if ($orderInfo['status'] != GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_RECEIVED) {
            throw new RestfulException('该回收订单还未接单，不可取消！');
        }

        // 取消订单（回收员主动取消）.
        GarbageOrderModel::query()->where(['order_no' => $orderNo])->update([
            'status' => GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_RECYCLER_CANCELED,
            'cancel_time' => date("Y-m-d H:i:s", time())
        ]);

        // 补偿用户5元代金券（逻辑调整为：跳转到回收页，给一个领取5元代金券的页面入口，24小时有效）
//        $userId = $orderInfo['user_id'];
//        app(CouponService::class)->obtainCoupon($userId, ActivityConst::COUPON_ID_5_YUAN, null);

        // 发起订单取消异步事件.
        $this->pushAsyncEventForCancelRecycleOrder($orderInfo);

        return true;
    }

    /**
     * 用户爽约订单（回收员主动取消）.
     *
     * @param string $orderNo
     *
     * @return bool
     *
     */
    public function cancelRecycleOrderByBreakPromise($orderNo)
    {
        // 判断用户此时是否可以操作取消.
        $orderInfo = $this->getGarbageRecycleOrderInfo(['order_no' => $orderNo], ['*']);
        if ($orderInfo['status'] != GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_RECEIVED) {
            throw new RestfulException('该回收订单还未接单，不可取消！');
        }

        // 取消订单（用户爽约取消）.
        GarbageOrderModel::query()->where(['order_no' => $orderNo])->update([
            'status' => GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_BREAK_PROMISE_CANCELED,
            'cancel_time' => date("Y-m-d H:i:s", time())
        ]);

        // 扣减用户信用分.
        $userId = $orderInfo['user_id'];
        app(AssertService::class)->decreaseCredit($userId, 10);

        // 发起订单取消异步事件.
        $this->pushAsyncEventForCancelRecycleOrder($orderInfo);

        return true;
    }


    /**
     * 系统取消回收订单（回收员上门超时取消）.
     *
     * @param string $orderNo
     *
     * @return bool
     *
     */
    public function cancelRecycleOrderBySystem($orderNo)
    {
        $orderInfo = $this->getGarbageRecycleOrderInfo(['order_no' => $orderNo]);
        if ($orderInfo['status'] != GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_RESERVED && $orderInfo['status'] != GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_RECEIVED) {
            throw new RestfulException('该订单不属于回收员未处理状态，不可系统取消！');
        }

        // 取消订单（系统取消）.
        GarbageOrderModel::query()->where(['order_no' => $orderInfo])->update([
            'status' => GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_TIMEOUT_CANCELED,
            'cancel_time' => date("Y-m-d H:i:s", time())
        ]);

        // 补偿用户5元代金券
//        $userId = $orderInfo['user_id'];
//        app(CouponService::class)->obtainCoupon($userId, ActivityConst::COUPON_ID_5_YUAN, null);

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
     * @param bool $withPage
     *
     * @return mixed
     *
     */
    public function getGarbageRecycleOrderList($where, $select, $orderBy, $page, $pageSize, $withPage = true)
    {
        $garbageRecycleOrderList = GarbageOrderModel::query()->macroQuery($where, $select, $orderBy, $page, $pageSize, $withPage);

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
        return GarbageOrderModel::query()->macroWhere($where)->macroSelect($select)->macroFirst();
    }

    /**
     * 通用查询订单数量.
     *
     * @param $where array 查询条件
     * @return int 符合条件的订单数
     */
    public function getGarbageRecycleOrderCount($where)
    {
        return GarbageOrderModel::query()->macroWhere($where)->count();
    }

    /**
     * 用户交易信息通知入队.
     *
     * @param $userId
     * @param $orderNo
     */
    public function generateNoticeRecord($userId, $orderNo)
    {
        $redis = Redis::connection('recycle');
        $noticeKey = RedisKeyConst::RECYCLE_NOTICE_USER;
        $userInfo = app(UserService::class)->getUserDetail($userId);
        $orderInfo = $this->getGarbageRecycleOrderInfo(['order_no' => $orderNo]);
        $noticeMsg = '用户"' . $userInfo['nickname'] . '"' . '在' . $orderInfo['finish_time'] . '回收收益' . $orderInfo['total_amount'] . '元';
        $notice = json_encode([
            'time' => time(),
            'msg' => $noticeMsg
        ]);
        if ($redis->hget($noticeKey, $userId)) {
            $redis->hdel($noticeKey, [$userId]);
        }
        $redis->hset($noticeKey, $userId, $notice);
    }

    /**
     * 修改预约单时间.
     *
     * @param string $orderNo
     * @param string $newRecycleDate
     * @param string $newRecyclingStartTime
     * @param string $newRecyclingEndTime
     * @return bool
     */
    public function modifyRecycleOrderAppointTime($orderNo, $newRecycleDate, $newRecyclingStartTime, $newRecyclingEndTime)
    {
        $orderInfo = $this->getGarbageRecycleOrderInfo(['order_no' => $orderNo]);

        // 校验修改时间必须在当前预约时间之后.
        $oldRecycleStartTime = $orderInfo['appoint_start_time'];
        $oldRecycleDate = date("Y-m-d", strtotime($oldRecycleStartTime));
        $oldRecyclePeriod = date('H:i', strtotime($orderInfo['appoint_start_time'])) . '-' . date('H:i', strtotime($orderInfo['appoint_end_time']));
        if (strtotime($newRecyclingStartTime) >= strtotime($newRecyclingEndTime)) {
            throw new RestfulException('回收开始时间必须小于回收结束时间！');
        }
        if (strtotime($oldRecycleStartTime) >= strtotime($newRecycleDate . ' ' . $newRecyclingStartTime)) {
            throw new RestfulException('修改时间必须在当前预约时间之后！');
        }

        // 修改订单时间.
        GarbageOrderModel::query()->where('order_no', $orderNo)->update([
            'appoint_start_time' => $newRecycleDate . ' ' . $newRecyclingStartTime,
            'appoint_end_time' => $newRecycleDate . ' ' . $newRecyclingEndTime
        ]);

        // 修改时间以后，需要修改redis订单数据（通过订单异步事件实现）.
        $newRecyclePeriod = $newRecyclingStartTime . '-' . $newRecyclingEndTime;
        event(new GarbageRecycleOrderCreateEvent([
            'order_no' => $orderNo,
            'recycle_date' => $newRecycleDate,
            'recycle_period' => $newRecyclePeriod
        ]));

        event(new GarbageRecycleOrderCancelEvent([
            'order_no' => $orderNo,
            'recycle_date' => $oldRecycleDate,
            'recycle_period' => $oldRecyclePeriod
        ]));

        return true;
    }

    /**
     * 领取补偿.
     *
     * @param string $orderNo
     * @return bool
     */
    public function receiveCompensate($orderNo)
    {
        $redis = Redis::connection('recycle');
        $redisKey = RedisKeyConst::ORDER_COMPENSATE_PREFIX . $orderNo;

        $orderInfo = $this->getGarbageRecycleOrderInfo(['order_no' => $orderNo]);
        if ($orderInfo['status'] != GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_RECYCLER_CANCELED && $orderInfo['status'] != GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_TIMEOUT_CANCELED) {
            throw new RestfulException('当前订单状态不满足领取补偿条件！');
        }

        $checkReceive = $redis->get($redisKey);
        if (!empty($checkReceive) && $checkReceive == 1) {
            throw new RestfulException('用户已领取过补偿，请勿重复领取！');
        }

        $userId = $orderInfo['user_id'];
        $expire = Carbon::now()->addDays(30);
        app(CouponService::class)->obtainCoupon($userId,ActivityConst::ORDER_COMPENSATE_COUPON_ID, $expire);
        $redis->set($redisKey, 1);

        return true;
    }

    /**
     * 发起回收订单取消异步事件.
     *
     * @param array $orderInfo
     *
     */
    private function pushAsyncEventForCancelRecycleOrder($orderInfo)
    {
        $recyclingDate = date('Y-m-d', strtotime($orderInfo['appoint_start_time']));
        $recyclePeriod = date('H:i', strtotime($orderInfo['appoint_start_time'])) . '-' . date('H:i', strtotime($orderInfo['appoint_end_time']));

        event(new GarbageRecycleOrderCancelEvent([
            'order_no' => $orderInfo['order_no'],
            'recycle_date' => $recyclingDate,
            'recycle_period' => $recyclePeriod
        ]));
    }

    // 膨胀券 膨胀比例获取.
    private function getExpandTimes($expandCouponId)
    {
        $expandTimes = 1;
        switch ($expandCouponId) {
            case 6:
                $expandTimes = 1.1;
                break;
            case 7:
                $expandTimes = 1.2;
                break;
            case 8:
                $expandTimes = 1.3;
                break;
            case 9:
                $expandTimes = 1.4;
                break;
            case 10:
                $expandTimes = 1.5;
                break;
        }
        return $expandTimes;
    }

    // 话费券 充值金额获取
    private function getChargeAmount($phoneCouponId) {
        $chargeAmount = 0;
        switch ($phoneCouponId) {
            case 1:
                $chargeAmount = 10;
                break;
            case 2:
                $chargeAmount = 20;
                break;
            case 3:
                $chargeAmount = 30;
                break;
            case 4:
                $chargeAmount = 50;
                break;
            case 5:
                $chargeAmount = 100;
                break;
        }
        return $chargeAmount;
    }
}
