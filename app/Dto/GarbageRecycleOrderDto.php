<?php

namespace App\Dto;

use App\Supports\Macro\Builder;

class GarbageRecycleOrderDto extends Dto
{
    /**
     * 创建垃圾回收订单.
     *
     * @param array $garbageRecycleOrderData
     *
     * @return int
     *
     */
    public function createRecycleOrder($garbageRecycleOrderData)
    {
        return $this->query->insertGetId([
            'order_no' => $garbageRecycleOrderData['order_no'],
            'user_id' => $garbageRecycleOrderData['user_id'],
            'recycler_id' => $garbageRecycleOrderData['recycler_id'],
            'site_id' => $garbageRecycleOrderData['site_id'],
            'village_id' => $garbageRecycleOrderData['village_id'],
            'village_floor_id' => $garbageRecycleOrderData['village_floor_id'],
            'address' => $garbageRecycleOrderData['address'],
            'status' => $garbageRecycleOrderData['status'],
            'recycling_start_time' => $garbageRecycleOrderData['recycling_start_time'],
            'recycling_end_time' => $garbageRecycleOrderData['recycling_end_time'],
            'total_amount' => $garbageRecycleOrderData['total_amount'],
            'remark' => $garbageRecycleOrderData['remark']
        ]);
    }

    /**
     * 通用修改垃圾回收订单信息.
     *
     * @param string $recycleOrderNo
     * @param array $updateOrderData
     *
     * @return int
     *
     */
    public function updateRecycleOrder($recycleOrderNo, $updateOrderData)
    {
        return $this->query->where('order_no', $recycleOrderNo)->update($updateOrderData);
    }

    /**
     * 通用查询回收订单列表.
     *
     * @param array $where
     * @param array $select
     * @param array $orderBy
     * @param int $page
     * @param int $limit
     * @param bool $withPage
     *
     * @return mixed
     */
    public function getRecycleOrderList($where, $select, $orderBy, $page = 1, $limit = Builder::PER_LIMIT, $withPage = true)
    {
        return $this->query->macroQuery($where, $select, $orderBy, $page, $limit, $withPage);
    }

    /**
     * 通用查询回收订单详情.
     *
     * @param array $where
     * @param array $select
     *
     * @return mixed
     *
     */
    public function getRecycleOrderInfo($where, $select = ['*'])
    {
        return $this->query->macroWhere($where)->select($select)->macroFirst();
    }
}