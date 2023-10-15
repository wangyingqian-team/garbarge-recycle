<?php

namespace App\Dto;

class GarbageRecycleOrderItemsDto extends Dto
{
    /**
     * 生成垃圾回收订单明细记录.
     *
     * @param array $garbageRecycleOrderItemsData
     *
     * @return mixed
     *
     */
    public function createRecycleOrderItems($garbageRecycleOrderItemsData)
    {
        foreach ($garbageRecycleOrderItemsData as $garbageRecycleOrderItem) {
            $this->query->insertGetId([
                'order_no' => $garbageRecycleOrderItem['order_no'],
                'garbage_category_id' => $garbageRecycleOrderItem['garbage_category_id'],
                'garbage_type_id' => $garbageRecycleOrderItem['garbage_type_id'],
                'price' => $garbageRecycleOrderItem['price'],
                'pre_weight' => $garbageRecycleOrderItem['pre_weight']
            ]);
        }
    }

    /**
     * 填充垃圾回收订单明细的实际重量和金额.
     *
     * @param string $orderNo
     * @param int $garbageTypeId
     * @param double $actualWeight
     * @param double $actualAmount
     *
     * @return int
     *
     */
    public function fillActualRecycleOrderItem($orderNo, $garbageTypeId, $actualWeight, $actualAmount)
    {
        return $this->query->where([
            'order_no' => $orderNo,
            'garbage_type_id' => $garbageTypeId
        ])->update([
            'actual_weight' => $actualWeight,
            'actual_amount' => $actualAmount
        ]);
    }
}