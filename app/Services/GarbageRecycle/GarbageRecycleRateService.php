<?php

namespace App\Services\GarbageRecycle;

use App\Dto\GarbageRecycleOrderDto;
use App\Dto\GarbageRecycleRateDto;
use App\Exceptions\RestfulException;
use App\Supports\Constant\GarbageRecycleConst;
use Illuminate\Support\Facades\DB;

class GarbageRecycleRateService
{
    /**
     * 生成回收订单评价.
     *
     * @param int $userId
     * @param string $orderNo
     * @param int $type
     * @param string $content
     * @param string $image
     *
     * @return bool
     *
     */
    public function addGarbageRecycleRate($userId, $orderNo, $type, $content, $image)
    {
        // 判断用户是否授权登录.
        if (empty($userId)) {
            throw new RestfulException('用户必须授权登录，请先授权登录！');
        }

        // 判断评价类型是否正确.
        if (!in_array($type, GarbageRecycleConst::GARBAGE_RECYCLE_RATE_TYPES)) {
            throw new RestfulException('请您选择好评或者差评！');
        }

        // 检查评价对应的订单.
        $orderInfo = app(GarbageRecycleOrderService::class)->getGarbageRecycleOrderInfo(['order_no' => $orderNo]);
        if (empty($orderInfo)) {
            throw new RestfulException('该回收订单不存在！');
        }
        if ($orderInfo['status'] != GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_FINISHED) {
            throw new RestfulException('该回收订单不属于已完成状态，不可评价！');
        }

        // 组装评价记录.
        $siteId = $orderInfo['site_id'];
        $rateData = [
            'user_id' => $userId,
            'order_no' => $orderNo,
            'site_id' => $siteId,
            'type' => $type,
            'content' => $content,
            'image' => $image
        ];

        DB::transaction(function () use ($rateData) {
            // 产生评价记录.
            app(GarbageRecycleRateDto::class)->createRate($rateData);

            // 更改订单状态.
            app(GarbageRecycleOrderDto::class)->updateRecycleOrder($rateData['order_no'], [
                'status' => GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_RATED,
                'rate_time' => date("Y-m-d H:i:s", time())
            ]);
        });

        return true;
    }

    /**
     * 查看回收订单评价列表.
     *
     * @param array $where
     * @param array $select
     * @param array $orderBy
     * @param int $page
     * @param int $pageSize
     *
     * @return mixed
     *
     */
    public function getGarbageRecycleRateList($where, $select, $orderBy, $page, $pageSize)
    {
        $garbageRecycleRateList = app(GarbageRecycleRateDto::class)->getRateList($where, $select, $orderBy, $page, $pageSize);

        return $garbageRecycleRateList;
    }
}