<?php

namespace App\Http\Controllers\Official;

use App\Services\Jifen\JifenItemService;
use App\Services\Jifen\JifenOrderService;
use App\Services\User\UserAssetsService;
use App\Supports\Constant\AssertConst;

class JifenController extends BaseController
{

    public function getItemList()
    {
        $title = $this->request->get('title');
        $jifen = $this->request->get('jifen');

        $where = [];
        $title && $where['title|like'] = $title;
        $jifen && $where['jifen_need|<='] = $jifen;

        $data = app(JifenItemService::class)->getItemList(
            $where,
            ['*'],
            ['jifen_need' => 'desc'],
            $this->page,
            $this->pageSize
        );

        return $this->success($data);
    }

    public function getItemDetail()
    {
        $itemId = $this->request->get('item_id');
        $data = app(JifenItemService::class)->getItemInfoById($itemId);
        $assets = app(UserAssetsService::class)->getUserAssets($this->userId);
        $data['user_jifen'] = $assets['jifen'];

        return $this->success($data);
    }

    public function createOrder()
    {
        $itemId = $this->request->get('item_id');
        $num = $this->request->get('num');
        $jifen = $this->request->get('jifen');
        $deliveryType = $this->request->get('delivery_type',  AssertConst::JI_FEN_DELIVERY_TWO);

        app(JifenOrderService::class)->create($this->userId, $itemId, $num, $jifen, $deliveryType);

        return $this->success();
    }

    public function getOrderList()
    {
        $where = [
            'user_id' => $this->userId
        ];

        $status = $this->request->get('status');
        !empty($status) && $where['status'] = $status;

        $data = app(JifenOrderService::class)->getOrderListWithPage($where, ['*'], [], $this->page, $this->pageSize);

        return $this->success($data);
    }

    public function getOrderDetail()
    {
        $data = app(JifenOrderService::class)->getOrderDetail($this->request->get('order_no'));

        return $this->success($data);
    }

}
