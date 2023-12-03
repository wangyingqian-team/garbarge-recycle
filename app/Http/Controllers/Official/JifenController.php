<?php

namespace App\Http\Controllers\Official;

use App\Services\JifenShop\JifenItemService;
use App\Services\JifenShop\JifenOrderService;
use App\Services\User\UserAssetsService;
use App\Supports\Constant\AssertConst;

class JifenController extends BaseController
{

    /**
     * 商品列表.
     *
     * @return mixed
     */
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
            ['create_time' => 'desc'],
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

    /**
     * 积分兑换商品下单.
     *
     * @return mixed
     * @throws \Throwable
     */
    public function createOrder()
    {
        $itemId = $this->request->get('item_id');
        $num = $this->request->get('num', 1);

        $result = app(JifenOrderService::class)->create(intval($this->userId), $itemId, $num);

        return $this->success($result);
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
