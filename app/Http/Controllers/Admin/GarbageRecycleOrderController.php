<?php

namespace App\Http\Controllers\Admin;

use App\Services\GarbageRecycle\GarbageRecycleOrderService;
use App\Services\GarbageRecycle\GarbageRecycleRateService;

/**
 * 管理后台 - 回收订单相关.
 */
class GarbageRecycleOrderController extends BaseController
{
    /**
     * 管理后回收仍订单列表.
     *
     * @return mixed
     *
     */
    public function getGarbageRecycleOrderList()
    {
        $siteId = $this->siteId;
        $startTime = $this->request->get('start_time');
        $endTime = $this->request->get('end_time');
        $where = [];
        !empty($siteId) && $where['recycler.site_id'] = $siteId;
        !empty($startTime) && $where['recycling_end_time|>='] = $startTime;
        !empty($endTime) && $where['recycling_start_time|<'] = $endTime;
        $select = ['*', 'recycler.*'];
        $orderBy = ['create_time' => 'desc'];
        $page = $this->page;
        $pageSize = $this->pageSize;

        $result = app(GarbageRecycleOrderService::class)->getGarbageRecycleOrderList($where, $select, $orderBy, $page, $pageSize);

        return $this->success($result);
    }

    /**
     * 管理后台回收订单详情.
     *
     * @return mixed
     *
     */
    public function getGarbageRecycleOrderInfo()
    {
        $orderNo = $this->request->get('order_no');

        $result = app(GarbageRecycleOrderService::class)->getGarbageRecycleOrderInfo(['order_no' => $orderNo]);

        return $this->success($result);
    }

    /**
     * 管理后台查看回收订单评价列表.
     *
     * @return mixed
     *
     */
    public function getGarbageRecycleRateList()
    {
        $siteId = $this->siteId;
        $startTime = $this->request->get('start_time');
        $endTime = $this->request->get('end_time');
        $type = $this->request->get('type');
        $page = $this->page;
        $pageSize = $this->pageSize;

        $where = [];
        !empty($siteId) && $where['site_id'] = $siteId;
        !empty($startTime) && $where['create_time|>='] = $startTime;
        !empty($endTime) && $where['create_time|<'] = $endTime;
        !empty($type) && $where['type'] = $type;
        $select = ['*', 'order.*'];
        $orderBy = ['create_time' => 'desc'];

        $result = app(GarbageRecycleRateService::class)->getGarbageRecycleRateList($where, $select, $orderBy, $page, $pageSize);

        return $this->success($result);
    }
}