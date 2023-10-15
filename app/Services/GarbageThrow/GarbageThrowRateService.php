<?php

namespace App\Services\GarbageThrow;

use App\Dto\GarbageThrowOrderDto;
use App\Dto\GarbageThrowRateDto;
use App\Exceptions\RestfulException;
use App\Supports\Constant\GarbageThrowConst;
use Illuminate\Support\Facades\DB;

class GarbageThrowRateService
{
    /**
     * 生成代仍订单评价.
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
    public function addGarbageThrowRate($userId, $orderNo, $type, $content, $image)
    {
        // 判断用户是否授权登录.
        if (empty($userId)) {
            throw new RestfulException('用户必须授权登录，请先授权登录！');
        }

        // 判断评价类型是否正确.
        if (!in_array($type, GarbageThrowConst::GARBAGE_THROW_RATE_TYPES)) {
            throw new RestfulException('请您选择好评或者差评！');
        }

        // 检查评价对应的订单.
        $orderInfo = app(GarbageThrowOrderService::class)->getGarbageThrowOrderInfo(['order_no' => $orderNo]);
        if (empty($orderInfo)) {
            throw new RestfulException('该代仍订单不存在！');
        }
        if ($orderInfo['status'] != GarbageThrowConst::GARBAGE_THROW_ORDER_STATUS_FINISHED) {
            throw new RestfulException('该代仍订单不属于已完成状态，不可评价！');
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
            app(GarbageThrowRateDto::class)->createRate($rateData);

            // 更改订单状态.
            app(GarbageThrowOrderDto::class)->updateThrowOrder($rateData['order_no'], [
                'status' => GarbageThrowConst::GARBAGE_THROW_ORDER_STATUS_RATED,
                'rate_time' => date("Y-m-d H:i:s", time())
            ]);
        });

        return true;
    }

    /**
     * 查看代仍订单评价列表.
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
    public function getGarbageThrowRateList($where, $select, $orderBy, $page, $pageSize)
    {
        $garbageThrowRateList = app(GarbageThrowRateDto::class)->getRateList($where, $select, $orderBy, $page, $pageSize);

        return $garbageThrowRateList;
    }
}