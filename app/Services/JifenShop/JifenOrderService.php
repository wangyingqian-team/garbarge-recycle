<?php

namespace App\Services\JifenShop;

use App\Events\JifenOrderCreateEvent;
use App\Exceptions\RestfulException;
use App\Models\JifenOrderModel;
use App\Services\Activity\CouponService;
use App\Services\User\AssertService;
use App\Services\User\UserService;
use App\Supports\Constant\JiFenConst;
use Illuminate\Support\Facades\DB;

class JifenOrderService
{
    /**
     * 积分兑换下单.
     *
     * @param $userId
     * @param $itemId
     * @param $num
     * @return bool
     * @throws \Throwable
     */
    public function create($userId, $itemId, $num)
    {
        $item = app(JifenItemService::class)->getItemInfo($itemId);
        if (empty($item)) {
            throw new RestfulException('商品信息不存在，请重新兑换！');
        }

        $jifenNeed = $item['jifen_need'];
        $jifenCost = $num * $jifenNeed;
        $userInfo = app(UserService::class)->getUserDetail($userId);

        if ($userInfo['asserts']['jifen'] < $jifenCost) {
            throw new RestfulException('用户积分不足');
        }

        $iData = [
            'order_no' => generate_order_no('E'),
            'user_id' => $userId,
            'title' => $item['title'],
            'jifen_need' => $jifenNeed,
            'num' => $num,
            'jifen_cost' => $jifenCost,
            'status' =>  JiFenConst::JI_FEN_ORDER_STATUS_UN_EXCHANGED
        ];

        DB::transaction(function () use($iData, $itemId, $item) {
            // 添加积分兑换订单.
            JifenOrderModel::query()->insert($iData);
            // 扣减积分.
            app(AssertService::class)->decreaseJifen($iData['user_id'], $iData['jifen_cost']);
            // 发放兑换券（一年有效期）.
            $expireTime = date('Y-m-d', strtotime('+1 year'));
            app(CouponService::class)->obtainCoupon($iData['user_id'], $item['coupon_id'], $expireTime);
        });

        // 下单异步事件推送.
        event(new JifenOrderCreateEvent($iData));

        return true;
    }

    /**
     * 积分兑换订单兑现.
     *
     * @param $orderNo string 商城订单号
     * @param $garbageOrderNo string 回收订单号
     * @return bool
     */
    public function exchangeJiFenOrder($orderNo, $garbageOrderNo)
    {
        // 检查订单状态
        $exchangeOrderInfo = $this->getOrderDetail($orderNo);
        if (empty($exchangeOrderInfo)) {
            throw new RestfulException('该兑换订单不存在，请稍后重试！');
        }

        if ($exchangeOrderInfo['status'] != JiFenConst::JI_FEN_ORDER_STATUS_UN_EXCHANGED) {
            throw new RestfulException('该订单已经兑换，请勿重复兑换！');
        }

        // 更新订单状态.
        JifenOrderModel::query()->where('order_no', $orderNo)->update([
            'status' => JiFenConst::JI_FEN_ORDER_STATUS_EXCHANGED,
            'garbage_order_no' => $garbageOrderNo
        ]);

        return true;
    }

    /**
     * 获取兑换订单列表带份页
     *
     * @param array $where
     * @param array $select
     * @param array $orderBy
     * @param int $page
     * @param int $limit
     * @param bool $withPage
     * @return mixed
     */
    public function getOrderListWithPage($where = [], $select = ['*'], $orderBy = [], $page = 0, $limit = 0, $withPage = true)
    {
        return JifenOrderModel::query()->macroQuery($where, $select, $orderBy, $page, $limit, $withPage);
    }

    /**
     * 获取兑换订单详情
     *
     * @param $orderNo
     * @return mixed
     */
    public function getOrderDetail($orderNo)
    {
        $detail = JifenOrderModel::query()->where('order_no', $orderNo)->macroFirst();
        return $detail;
    }

}
