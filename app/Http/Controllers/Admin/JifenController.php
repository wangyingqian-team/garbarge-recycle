<?php

namespace App\Http\Controllers\Admin;

use App\Services\Jifen\JifenItemService;
use App\Services\Jifen\JifenOrderService;

class JifenController extends BaseController
{

    public function createItem()
    {
        $data = [
            'title' => $this->request->get('title'),
            'primary_image' => $this->request->get('primary_image'),
            'jifen_need' => $this->request->get('jifen_need'),
            'unit_name' => $this->request->get('unit_name')
        ];

        app(JifenItemService::class)->create($data);
    }

    public function updateItem()
    {
        $id = $this->request->get('id');
        $data = [
            'title' => $this->request->get('title'),
            'primary_image' => $this->request->get('primary_image'),
            'jifen_need' => $this->request->get('jifen_need'),
            'unit_name' => $this->request->get('unit_name')
        ];

        app(JifenItemService::class)->update($id, $data);
    }

    public function deleteItem()
    {
        $id = $this->request->get('id');
        app(JifenItemService::class)->delete($id);
    }

    public function getItemList()
    {
        $title = $this->request->get('title');

        $where = [];
        $title && $where['title|like'] = $title;

        $data = app(JifenItemService::class)->getItemList(
            $where,
            ['*'],
            ['create_time' => 'desc'],
            $this->page,
            $this->pageSize
        );

        return $this->success($data);
    }


    public function getOrderList()
    {
        $title = $this->request->get('title');
        $status = $this->request->get('status');
        $startTime = $this->request->get('start_time');
        $endTime = $this->request->get('end_time');

        $where = [];
        $status && $where['status'] = $status;
        $title && $where['title|like'] = $title;
        $startTime && $where['create_time|>='] = $startTime;
        $endTime && $where['create_time|<='] = $endTime;

        $data = app(JifenOrderService::class)->getOrderListWithPage(
            $where,
            ['*'],
            ['create_time' => 'desc'],
            $this->page,
            $this->pageSize
        );

        return $this->success($data);
    }

    public function getOrderDetail()
    {
        $data = app(JifenOrderService::class)->getOrderDetail($this->request->get('order_no'));

        return $this->success($data);
    }

}
