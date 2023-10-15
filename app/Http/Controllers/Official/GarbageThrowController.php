<?php

namespace App\Http\Controllers\Official;

use App\Exceptions\RestfulException;
use App\Services\Common\ConfigService;
use App\Services\GarbageThrow\GarbageThrowOrderService;
use App\Services\GarbageThrow\GarbageThrowRateService;
use App\Supports\Constant\ConfigConst;

class GarbageThrowController extends BaseController
{
    /**
     * 代仍垃圾种类列表.
     *
     * @return mixed
     */
    public function getThrowGarbageTypes()
    {
        $result = app(ConfigService::class)->getConfig(ConfigConst::THROW_GARBAGE_TYPES);
        return $this->success($result);
    }

    /**
     * 用户选择当前可预约的代仍时间段列表.
     *
     * @return mixed
     */
    public function getThrowableTimePeriodList()
    {
        $throwingDate = $this->request->get('throwing_date');
        $villageId = $this->request->get('village_id');

        $result = app(GarbageThrowOrderService::class)->getThrowableTimePeriodList($throwingDate, $villageId);

        return $this->success($result);
    }

    /**
     * 用户预约垃圾代仍.
     *
     * @return mixed
     *
     */
    public function createGarbageThrowOrder()
    {
        $throwType = $this->request->post('throw_type');
        $villageId = $this->request->post('village_id');
        $villageFloorId = $this->request->post('village_floor_id');
        $address = $this->request->post('address');
        $throwingDate = $this->request->post('throwing_date');
        $throwingStartTime = $this->request->post('throwing_start_time');
        $throwingEndTime = $this->request->post('throwing_end_time');
        $remark = $this->request->post('remark');
        $couponIds = $this->request->post('coupon_ids');
        $couponIds = json_decode($couponIds, true);

        $result = app(GarbageThrowOrderService::class)->createGarbageThrowOrder(
            $this->userId, $throwType, $villageId, $villageFloorId, $address, $throwingDate, $throwingStartTime, $throwingEndTime, $remark, $couponIds
        );

        return $this->success($result);
    }

    /**
     * 用户取消代仍订单.
     *
     * @return mixed
     *
     */
    public function cancelGarbageThrowOrderByUser()
    {
        $orderNo = $this->request->get('order_no');

        $result = app(GarbageThrowOrderService::class)->cancelGarbageThrowOrderByUser($orderNo);

        return $this->success($result);
    }

    /**
     * 用户完成代仍订单.
     *
     * @return mixed
     *
     */
    public function finishGarbageThrowOrder()
    {
        $orderNo = $this->request->get('order_no');

        $result = app(GarbageThrowOrderService::class)->finishGarbageThrowOrder($orderNo);

        return $this->success($result);
    }

    /**
     * 用户评价代仍订单.
     *
     * @return mixed
     *
     */
    public function rateGarbageThrowOrder()
    {
        $userId = $this->userId;
        $orderNo = $this->request->post('order_no');
        $type = $this->request->post('type');
        $content = $this->request->post('content');
        $image = $this->request->post('image');

        $result = app(GarbageThrowRateService::class)->addGarbageThrowRate($userId, $orderNo, $type, $content, $image);

        return $this->success($result);
    }

    /**
     * 用户代仍订单列表.
     *
     * @return mixed
     *
     */
    public function getUserThrowOrderList()
    {
        $userId = $this->userId;
        $page = $this->page;
        $pageSize = $this->pageSize;
        $status = $this->request->get('status');

        $where = ['user_id' => $userId];
        !empty($status) && $where['status'] = $status;
        $select = ['*', 'recycler.*'];
        $orderBy = ['create_time' => 'desc'];

        $result = app(GarbageThrowOrderService::class)->getGarbageThrowOrderList($where, $select, $orderBy, $page, $pageSize);

        return $this->success($result);
    }

    /**
     * 用户代仍订单详情.
     *
     * @return mixed
     *
     */
    public function getUserThrowOrderInfo()
    {
        $userId = $this->userId;
        $orderNo = $this->request->get('order_no');
        $result = app(GarbageThrowOrderService::class)->getGarbageThrowOrderInfo([
            'order_no' => $orderNo,
            'user_id' => $userId
        ]);

        return $this->success($result);
    }

    /**
     * 用户我的评价列表.
     *
     * @return mixed
     *
     */
    public function getUserGarbageThrowRateList()
    {
        $userId = $this->userId;
        $page = $this->page;
        $pageSize = $this->pageSize;

        $where = ['user_id' => $userId];
        $select = ['*', 'order.*'];
        $orderBy = ['create_time' => 'desc'];

        $result = app(GarbageThrowRateService::class)->getGarbageThrowRateList($where, $select, $orderBy, $page, $pageSize);

        return $this->success($result);
    }

    /**
     * 回收员取消代仍订单.
     *
     * @return mixed
     *
     */
    public function cancelGarbageThrowOrderByRecycler()
    {
        $orderNo = $this->request->get('order_no');

        $result = app(GarbageThrowOrderService::class)->cancelGarbageThrowOrderByRecycler($orderNo);

        return $this->success($result);
    }

    /**
     * 回收员代仍订单列表.
     *
     * @return mixed
     *
     */
    public function getRecyclerThrowOrderList()
    {
        $recyclerId = $this->recyclerId;
        $page = $this->page;
        $pageSize = $this->pageSize;

        if (empty($recyclerId)) {
            throw new RestfulException('回收员暂未登录！');
        }

        $where = ['recycler_id' => $recyclerId];
        !empty($status) && $where['status'] = $status;
        $select = ['*', 'recycler.*'];
        $orderBy = ['create_time' => 'desc'];

        $result = app(GarbageThrowOrderService::class)->getGarbageThrowOrderList($where, $select, $orderBy, $page, $pageSize);

        return $this->success($result);
    }

    /**
     * 回收员代仍订单详情.
     *
     * @return mixed
     *
     */
    public function getRecyclerThrowOrderInfo()
    {
        $recyclerId = $this->recyclerId;
        $orderNo = $this->request->get('order_no');

        if (empty($recyclerId)) {
            throw new RestfulException('回收员暂未登录！');
        }

        $result = app(GarbageThrowOrderService::class)->getGarbageThrowOrderInfo([
            'order_no' => $orderNo,
            'recycler_id' => $recyclerId
        ]);

        return $this->success($result);
    }
}