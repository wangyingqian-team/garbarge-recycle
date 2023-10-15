<?php

namespace App\Dto;

class JifenOrderDto extends Dto
{
    public function create($data)
    {
        $iData = [
            'order_no' => $data['order_no'],
            'user_id' => $data['user_id'],
            'title' => $data['title'],
            'image' => $data['image'],
            'jifen_need' => $data['jifen_need'],
            'num' => $data['num'],
            'jifen_cost' => $data['jifen_cost'],
            'status' => $data['status'],
            'remark' => $data['remark']
        ];

        return $this->query->insert($iData);
    }

    public function getOrderList($where, $select, $orderBy, $page, $limit, $withPage)
    {
        return $this->query->macroQuery($where, $select, $orderBy, $page, $limit, $withPage);
    }

    public function getOrderDetail($orderNo)
    {
        return $this->query->where('order_no', $orderNo)->macroFirst();
    }
}
