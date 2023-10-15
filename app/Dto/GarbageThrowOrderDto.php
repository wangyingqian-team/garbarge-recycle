<?php
namespace App\Dto;

use App\Supports\Macro\Builder;

class GarbageThrowOrderDto extends Dto
{
    /**
     * 创建垃圾代仍订单.
     *
     * @param array $throwOrderInfo
     *
     * @return int
     *
     */
    public function createThrowOrder($throwOrderInfo)
    {
        return $this->query->insertGetId([
            'order_no' => $throwOrderInfo['order_no'],
            'user_id' => $throwOrderInfo['user_id'],
            'recycler_id' => $throwOrderInfo['recycler_id'],
            'garbage_throw_type' => $throwOrderInfo['throw_type'],
            'site_id' => $throwOrderInfo['site_id'],
            'village_id' => $throwOrderInfo['village_id'],
            'village_floor_id' => $throwOrderInfo['village_floor_id'],
            'address' => $throwOrderInfo['address'],
            'status' => $throwOrderInfo['status'],
            'throwing_start_time' => $throwOrderInfo['throwing_start_time'],
            'throwing_end_time' => $throwOrderInfo['throwing_end_time'],
            'remark' => $throwOrderInfo['remark']
        ]);
    }

    /**
     * 通用修改垃圾代仍订单信息.
     *
     * @param string $throwOrderNo
     * @param array $updateOrderData
     *
     * @return int
     *
     */
    public function updateThrowOrder($throwOrderNo, $updateOrderData)
    {
        return $this->query->where('order_no', $throwOrderNo)->update($updateOrderData);
    }

    /**
     * 通用查询垃圾代仍订单数量.
     *
     * @param array $where
     *
     * @return int
     *
     */
    public function getThrowOrderCount($where)
    {
        return $this->query->where($where)->count();
    }

    /**
     * 通用查询代仍订单列表.
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
    public function getThrowOrderList($where, $select, $orderBy, $page = 1, $limit = Builder::PER_LIMIT, $withPage = true)
    {
        return $this->query->macroQuery($where, $select, $orderBy, $page, $limit, $withPage);
    }

    /**
     * 通用查询代仍订单详情.
     *
     * @param array $where
     * @param array $select
     *
     * @return mixed
     *
     */
    public function getThrowOrderInfo($where, $select = ['*'])
    {
        return $this->query->macroWhere($where)->macroSelect($select)->macroFirst();
    }
}