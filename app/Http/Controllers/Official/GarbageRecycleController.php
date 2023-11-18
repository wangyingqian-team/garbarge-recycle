<?php

namespace App\Http\Controllers\Official;

use App\Services\GarbageRecycle\GarbageCategoryService;
use App\Services\GarbageRecycle\GarbageRecycleOrderService;

class GarbageRecycleController extends BaseController
{
    /**
     * 获取垃圾分类与价格
     *
     * @return mixed
     *
     */
    public function getGarbageTypePriceList()
    {
        $select = [
            'id', 'name', 'order', 'create_time', 'update_time', 'type.id', 'type.category_id', 'type.unit_name',
            'type.name', 'type.icon', 'type.recycling_price', 'type.create_time', 'type.update_time'
        ];
        $filters = [];
        $orderBys = ['order' => 'asc'];


        $result = app(GarbageCategoryService::class)->getGarbageCategoryList($filters, $select, $orderBys);

        return $this->success($result);
    }

    /**
     * 查询指定小区指定日期的可回收时间段列表.
     *
     * @return mixed
     */
    public function getRecycleTimePeriodList()
    {
        $result = app(GarbageRecycleOrderService::class)->getRecycleTimePeriodList();

        return $this->success($result);
    }

    /**
     * 创建回收订单.
     *
     * @return mixed
     *
     */
    public function createGarbageRecycleOrder()
    {
        $requestBody = $this->request->all();

        $addressId = $requestBody['address_id'];
        $recyclingDate = $requestBody['recycling_date'];
        $recyclingStartTime = $requestBody['recycling_start_time'];
        $recyclingEndTime = $requestBody['recycling_end_time'];
        $predictWeight = $requestBody['predict_weight'];
        $promotionInfo = $requestBody['promotion_info'];

        $result = app(GarbageRecycleOrderService::class)->createGarbageRecycleOrder(
            $this->userId, $addressId, $recyclingDate,$recyclingStartTime, $recyclingEndTime, $predictWeight, $promotionInfo
        );

        return $this->success($result);
    }

    /**
     * 用户确认回收订单（确认为已完成）.
     *
     * @return mixed
     *
     */
    public function confirmRecycleOrderByUser()
    {
        $orderNo = $this->request->get('order_no');

        $result = app(GarbageRecycleOrderService::class)->confirmRecycleOrderByUser($orderNo);

        return $this->success($result);
    }

    /**
     * 用户取消回收订单（用户预约取消）.
     *
     * @return mixed
     *
     */
    public function cancelRecycleOrderByUserReserve()
    {
        $orderNo = $this->request->get('order_no');

        $result = app(GarbageRecycleOrderService::class)->cancelRecycleOrderByUserReserve($orderNo);

        return $this->success($result);
    }

    /**
     * 用户取消回收订单（用户确认取消）.
     *
     * @return mixed
     *
     */
    public function cancelRecycleOrderByUserConfirm()
    {
        $orderNo = $this->request->get('order_no');

        $result = app(GarbageRecycleOrderService::class)->cancelRecycleOrderByUserConfirm($orderNo);

        return $this->success($result);
    }

    /**
     * 用户评价回收订单.
     *
     * @return mixed
     *
     */
    public function rateGarbageRecycleOrder()
    {
        $userId = $this->userId;
        $orderNo = $this->request->post('order_no');
        $type = $this->request->post('type');
        $content = $this->request->post('content');
        $image = $this->request->post('image');

        $result = app(GarbageRecycleRateService::class)->addGarbageRecycleRate($userId, $orderNo, $type, $content, $image);

        return $this->success($result);
    }

    /**
     * 用户我的评价列表.
     *
     * @return mixed
     *
     */
    public function getUserRecycleRateList()
    {
        $userId = $this->userId;
        $page = $this->page;
        $pageSize = $this->pageSize;

        $where = ['user_id' => $userId];
        $select = ['*', 'order.*'];
        $orderBy = ['create_time' => 'desc'];

        $result = app(GarbageRecycleRateService::class)->getGarbageRecycleRateList($where, $select, $orderBy, $page, $pageSize);

        return $this->success($result);
    }

    /**
     * 用户回收订单列表.
     *
     * @return mixed
     *
     */
    public function getUserRecycleOrderList()
    {
        $userId = $this->userId;
        $page = $this->page;
        $pageSize = $this->pageSize;
        $status = $this->request->get('status');

        $where = ['user_id' => $userId];
        !empty($status) && $where['status'] = $status;
        $select = ['*', 'recycler.*'];
        $orderBy = ['create_time' => 'desc'];

        $result = app(GarbageRecycleOrderService::class)->getGarbageRecycleOrderList($where, $select, $orderBy, $page, $pageSize);

        return $this->success($result);
    }

    /**
     * 用户回收订单详情.
     *
     * @return mixed
     *
     */
    public function getUserRecycleOrderInfo()
    {
        $userId = $this->userId;
        $orderNo = $this->request->get('order_no');
        $result = app(GarbageRecycleOrderService::class)->getGarbageRecycleOrderInfo([
            'order_no' => $orderNo,
            'user_id' => $userId
        ]);

        return $this->success($result);
    }

    /**
     * 回收员确认回收订单（确认为待完成）.
     *
     * @return mixed
     *
     */
    public function confirmRecycleOrderByRecycler()
    {
        $orderNo = $this->request->post('order_no');
        $orderItems = $this->request->post('order_items');
        $orderItems = json_decode($orderItems, true);
        $result = app(GarbageRecycleOrderService::class)->confirmRecycleOrderByRecycler($orderNo, $orderItems);

        return $this->success($result);
    }

    /**
     * 回收员取消回收订单.
     *
     * @return mixed
     *
     */
    public function cancelRecycleOrderByRecycler()
    {
        $orderNo = $this->request->get('order_no');

        $result = app(GarbageRecycleOrderService::class)->cancelRecycleOrderByRecycler($orderNo);

        return $this->success($result);
    }

    /**
     * 回收员回收订单列表.
     *
     * @return mixed
     *
     */
    public function getRecyclerRecycleOrderList()
    {
        $recyclerId = $this->recyclerId;
        $page = $this->page;
        $pageSize = $this->pageSize;

        $where = ['recycler_id' => $recyclerId];
        !empty($status) && $where['status'] = $status;
        $select = ['*', 'recycler.*'];
        $orderBy = ['create_time' => 'desc'];

        $result = app(GarbageRecycleOrderService::class)->getGarbageRecycleOrderList($where, $select, $orderBy, $page, $pageSize);

        return $this->success($result);
    }

    /**
     * 回收员回收订单详情.
     *
     * @return mixed
     *
     */
    public function getRecyclerRecycleOrderInfo()
    {
        $recyclerId = $this->recyclerId;
        $orderNo = $this->request->get('order_no');
        $result = app(GarbageRecycleOrderService::class)->getGarbageRecycleOrderInfo([
            'order_no' => $orderNo,
            'recycler_id' => $recyclerId
        ]);

        return $this->success($result);
    }

}
