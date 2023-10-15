<?php

namespace App\Http\Controllers\Admin;

use App\Services\GarbageThrow\GarbageThrowOrderService;
use App\Services\GarbageThrow\GarbageThrowRateService;

/**
 * 管理后台 - 代仍订单相关
 */
class GarbageThrowOrderController extends BaseController
{
    /**
     * 管理后台代仍订单列表.
     *
     * @return mixed
     *
     */
    public function getGarbageThrowOrderList()
    {
        $siteId = $this->siteId;
        $startTime = $this->request->get('start_time');
        $endTime = $this->request->get('end_time');
        $where = [];
        !empty($siteId) && $where['recycler.site_id'] = $siteId;
        !empty($startTime) && $where['throwing_end_time|>='] = $startTime;
        !empty($endTime) && $where['throwing_start_time|<'] = $endTime;
        $select = ['*', 'recycler.*'];
        $orderBy = ['create_time' => 'desc'];
        $page = $this->page;
        $pageSize = $this->pageSize;

        $result = app(GarbageThrowOrderService::class)->getGarbageThrowOrderList($where, $select, $orderBy, $page, $pageSize);

        return $this->success($result);
    }

    /**
     * 管理后台代仍订单详情.
     *
     * @return mixed
     *
     */
    public function getGarbageThrowOrderInfo()
    {
        $orderNo = $this->request->get('order_no');

        $result = app(GarbageThrowOrderService::class)->getGarbageThrowOrderInfo(['order_no' => $orderNo]);

        return $this->success($result);
    }

    /**
     * 管理后台查看代仍订单评价列表.
     *
     * @return mixed
     *
     */
    public function getGarbageThrowRateList()
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

        $result = app(GarbageThrowRateService::class)->getGarbageThrowRateList($where, $select, $orderBy, $page, $pageSize);

        return $this->success($result);
    }
}