<?php

namespace App\Services\GarbageRecycle;

use App\Dto\GarbageRecycleOrderDto;
use App\Events\GarbageRecycleOrderCancelEvent;
use App\Events\GarbageRecycleOrderCreateEvent;
use App\Events\GarbageRecycleOrderFinishEvent;
use App\Exceptions\RestfulException;
use App\Models\GarbageOrderModel;
use App\Services\Activity\CouponService;
use App\Services\Activity\InvitationService;
use App\Services\Common\ConfigService;
use App\Services\User\AddressService;
use App\Services\User\AssertService;
use App\Services\User\UserService;
use App\Services\User\VillageService;
use App\Supports\Constant\ActivityConst;
use App\Supports\Constant\GarbageRecycleConst;
use App\Supports\Constant\RedisKeyConst;
use App\Supports\Constant\UserConst;
use Illuminate\Support\Facades\Redis;

class GarbageRecycleOrderService
{
    /**
     * 查询指定小区的可回收时间段列表.
     *
     * @param string $recyclingDate
     * @param int $villageId
     *
     * @return mixed
     */
    public function getRecycleTimePeriodList($recyclingDate, $villageId)
    {
        // 判断用户选择的小区是否开放.
        $villageInfo = app(VillageService::class)->getVillageInfo($villageId);
        if (empty($villageInfo)) {
            throw new RestfulException('该小区信息不存在，请重新选择！');
        }

        if ($villageInfo['is_active'] == UserConst::VILLAGE_STATUS_INACTIVE) {
            throw new RestfulException('抱歉，该小区目前暂未开通回收服务，请耐心期待！');
        }

        // 查询所有支持的回收时间段.
        $allRecyclePeriod = app(ConfigService::class)->getConfig(GarbageRecycleConst::GARBAGE_RECYCLE_APPOINT_PERIOD);

        // 过滤已经满约的回收时间段，返回可预约的时间段.
        // 预约已满的时间段，显示一下
        $recycleOrderNumPerTime = app(ConfigService::class)->getConfig(GarbageRecycleConst::GARBAGE_RECYCLE_MAX_ORDERS_PER_PERIOD);
        $redis = Redis::connection('recycle');
        return array_map(function ($period) use ($recyclingDate, $recycleOrderNumPerTime, $redis) {
            $recyclePeriod = date('H:i', strtotime($period['start_time'])) . '-' . date('H:i', strtotime($period['end_time']));
            $recyclerOrderCount = $redis->hget(RedisKeyConst::RECYCLE_RECYCLER_ORDER_COUNT_PREFIX . ':' . $recyclingDate, $recyclePeriod);
            return [
                'time_period' => $recyclePeriod,
                'is_full' => $recyclerOrderCount >= $recycleOrderNumPerTime
            ];
        }, $allRecyclePeriod);

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
     *              -> exchange_coupon_id: 物品兑换券id
     *              -> phone_coupon_id: 话费券id
     *              -> expand_coupon_id: 膨胀券id
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

        // 检验回收垃圾合法性.
        $garbageTotalAmount = 0.00;// 订单总额（预估，待回收员确认的时候再修改）

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
        $checkOngoingOrder = GarbageOrderModel::query()->where('user_id', $userId)->whereIn('status', GarbageRecycleConst::GARBAGE_RECYCLE_MAX_ORDERS_PER_PERIOD);
        if (!empty($checkOngoingOrder)) {
            throw new RestfulException('您已经预约上门回收，不可重复预约！');
        }


        // 创建订单.
        $orderNo = generate_order_no('R');
        $orderStatus = GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_RESERVED;
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
     * 回收员完成回收订单.
     *
     * @param string $orderNo 订单号
     * @param string $actualWeight 订单实际重量
     * @param double $recycleAmount 订单实际回收金额
     *
     * @return bool
     *
     * @throws \Throwable
     */
    public function finishGarbageRecycleOrder($orderNo, $actualWeight, $recycleAmount)
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
            app(InvitationService::class)->costBean($userId, $orderNo, $promotionInfo['bean_num']);
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
        if (!empty($promotionInfo['exchange_coupon_id'])) {
            app(CouponService::class)->useCoupon($userId, $promotionInfo['exchange_coupon_id'], $orderNo);
        }

        // 修改订单完成数据.
        GarbageOrderModel::query()->where('order_no', $orderNo)->update([
            'status' => GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_FINISHED,
            'actual_weight' => $actualWeight,
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
        // 判断用户此时是否可以操作取消.
        $orderInfo = $this->getGarbageRecycleOrderInfo(['order_no' => $orderNo], ['*']);
        if ($orderInfo['status'] != GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_RESERVED) {
            throw new RestfulException('该回收订单已经被接单，不可取消！');
        }
        if (strtotime($orderInfo['appoint_start_time']) <= time()) {
            throw new RestfulException('当前已经到了预约回收时间，不可取消！');
        }

        // 取消订单（用户预约取消）.
        GarbageOrderModel::query()->where(['order_no' => $orderInfo])->update([
            'status' => GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_USER_CANCELED,
            'cancel_time' => date("Y-m-d H:i:s", time())
        ]);

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
        GarbageOrderModel::query()->where(['order_no' => $orderInfo])->update([
            'status' => GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_RECYCLER_CANCELED,
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
        GarbageOrderModel::query()->where(['order_no' => $orderInfo])->update([
            'status' => GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_BREAK_PROMISE_CANCELED,
            'cancel_time' => date("Y-m-d H:i:s", time())
        ]);

        // 扣减用户信用分.
        $userId = $orderInfo['userId'];
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
        if ($orderInfo['status'] != GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_RECEIVED || $orderInfo['status'] != GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_RECEIVED) {
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
        return GarbageOrderModel::query()->macroWhere($where)->select($select)->macroFirst();
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
        $orderInfo = $this->getGarbageRecycleOrderInfo(['order_no' => $orderNo], ['order_no', 'total_amount']);
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
