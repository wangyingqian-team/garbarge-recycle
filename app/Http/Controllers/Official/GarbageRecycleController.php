<?php

namespace App\Http\Controllers\Official;

use App\Services\GarbageRecycle\GarbageCategoryService;
use App\Services\GarbageRecycle\GarbageRecycleOrderService;
use App\Services\User\UserService;
use App\Supports\Constant\GarbageRecycleConst;

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
        $filters = ['id|!=' => GarbageRecycleConst::GARBAGE_CATEGORY_TYPE_ID_OTHERS];
        $orderBys = ['order' => 'asc'];


        $result = app(GarbageCategoryService::class)->getGarbageCategoryList($filters, $select, $orderBys);

        return $this->success($result);
    }

    /**
     * 查询可回收时间段列表.
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
        $promotionInfo = $requestBody['promotion_info'] ?? [];

        $result = app(GarbageRecycleOrderService::class)->createGarbageRecycleOrder(
            $this->userId, $addressId, $recyclingDate,$recyclingStartTime, $recyclingEndTime, $predictWeight, $promotionInfo
        );

        return $this->success($result);
    }

    /**
     * 回收员接单.
     *
     * @return mixed
     *
     */
    public function receiveRecycleOrder()
    {
        $orderNo = $this->request->get('order_no');

        $result = app(GarbageRecycleOrderService::class)->receiveGarbageRecycleOrder($orderNo);

        return $this->success($result);
    }

    /**
     * 回收员上门.
     *
     * @return mixed
     */
    public function startRecycleOrder()
    {
        $orderNo = $this->request->get('order_no');

        $result = app(GarbageRecycleOrderService::class)->startGarbageRecycleOrder($orderNo);

        return $this->success($result);
    }

    /**
     * 回收员设置订单分类明细.
     *
     * @return mixed
     */
    public function setRecycleOrderDetails()
    {
        $requestBody = $this->request->all();
        $orderNo = $requestBody['order_no'];
        $orderDetails = $requestBody['order_details'];
        $result = app(GarbageRecycleOrderService::class)->setGarbageRecycleOrderDetails($orderNo, $orderDetails);
        return $this->success($result);
    }

    /**
     * 回收员订单完成.
     *
     * @return mixed
     * @throws \Throwable
     */
    public function finishRecycleOrder()
    {
        $orderNo = $this->request->get('order_no');
        $recycleAmount = $this->request->get('recycle_amount');

        $result = app(GarbageRecycleOrderService::class)->finishGarbageRecycleOrder($orderNo, $recycleAmount);

        return $this->success($result);
    }

    /**
     * 用户取消回收订单（用户预约取消）.
     *
     * @return mixed
     *
     */
    public function cancelRecycleOrderByUser()
    {
        $orderNo = $this->request->get('order_no');

        $result = app(GarbageRecycleOrderService::class)->cancelRecycleOrderByUser($orderNo);

        return $this->success($result);
    }

    /**
     * 回收员取消回收订单（用户爽约取消）.
     *
     * @return mixed
     *
     */
    public function cancelRecycleOrderByBp()
    {
        $orderNo = $this->request->get('order_no');

        $result = app(GarbageRecycleOrderService::class)->cancelRecycleOrderByBreakPromise($orderNo);

        return $this->success($result);
    }

    /**
     * 用户回收历史订单列表.
     *
     * @return mixed
     *
     */
    public function getUserRecycleOrderList()
    {
        $userId = $this->userId;
        $page = $this->page;
        $pageSize = $this->pageSize;
        $status = GarbageRecycleConst::GARBAGE_RECYCLE_ORDER_STATUS_FINISHED;

        $where = ['user_id' => $userId];
        !empty($status) && $where['status'] = $status;
        $select = ['*', 'details.*', 'address.*'];
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
        $select = ['*', 'address.*'];
        $result = app(GarbageRecycleOrderService::class)->getGarbageRecycleOrderInfo([
            'order_no' => $orderNo,
            'user_id' => $userId
        ], $select);

        $recyclerInfo = app(UserService::class)->getUserBasicInfo(GarbageRecycleConst::RECYCLER_ID);
        $result['recycler'] = $recyclerInfo;

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
//        $recyclerId = $this->recyclerId;
        $page = $this->page;
        $pageSize = $this->pageSize;
        $status = $this->request->get('status');
        $date = $this->request->get('date');

//        $where = ['recycler_id' => $recyclerId];
        !empty($status) && $where['status'] = $status;
        if (!empty($date)) {
            $where['appoint_start_time|>='] = date("Y-m-d 00:00:00", strtotime($date));
            $where['appoint_start_time|<='] = date('Y-m-d 23:59:59', strtotime($date));
        }
        $select = ['*', 'details.*', 'address.*'];
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
        $orderNo = $this->request->get('order_no');
        $result = app(GarbageRecycleOrderService::class)->getGarbageRecycleOrderInfo([
            'order_no' => $orderNo
        ], ['*', 'details.*', 'address.*']);

        return $this->success($result);
    }

    /**
     * 修改预约时间.
     */
    public function modifyRecycleOrderAppointTime()
    {
        $orderNo = $this->request->get('order_no');
        $newRecycleDate = $this->request->get('new_recycle_date');
        $newRecyclingStartTime = $this->request->get('new_recycle_start_time');
        $newRecyclingEndTime = $this->request->get('new_recycle_end_time');

        $result = app(GarbageRecycleOrderService::class)->modifyRecycleOrderAppointTime(
            $orderNo, $newRecycleDate, $newRecyclingStartTime, $newRecyclingEndTime
        );

        return $this->success($result);
    }

}
