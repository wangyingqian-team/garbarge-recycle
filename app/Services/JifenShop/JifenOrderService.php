<?php

namespace App\Services\JifenShop;

use App\Dto\JifenOrderDto;
use App\Events\JifenOrderCreateEvent;
use App\Exceptions\RestfulException;
use App\Services\User\UserAssetsService;
use App\Services\User\UserService;
use App\Supports\Constant\ AssertConst;
use Illuminate\Support\Facades\DB;

class JifenOrderService
{
    /** @var JifenOrderDto */
    public $dto;

    public function __construct()
    {
        $this->dto = app(JifenOrderDto::class);
    }


    /**
     * @param $userId
     * @param $itemId
     * @param $num
     * @param $jifen
     * @return bool
     * @throws \Throwable
     */
    public function create($userId, $itemId, $num, $jifen, $deliveryType)
    {
        $userAssets = app(UserAssetsService::class)->getUserAssets($userId);

        if ($userAssets['jifen'] < $jifen) {
            throw new RestfulException('用户积分不足');
        }

        $item = app(JifenItemService::class)->getItemInfoById($itemId, false);
        $iData = [
            'order_no' => generate_order_no($userId,  AssertConst::JI_FEN_ORDER_PREFIX),
            'user_id' => $userId,
            'title' => $item['title'],
            'image' => $item['primary_image'],
            'jifen_need' => $item['jifen_need'],
            'num' => $num,
            'delivery_type' => $deliveryType,
            'jifen_cost' => $jifen,
            'status' =>  AssertConst::JI_FEN_ORDER_WAIT_STATUS,
            'remark' => ''
        ];

        DB::transaction(function () use($iData, $itemId) {
            $this->dto->create($iData);
            //扣减积分
            app(UserAssetsService::class)->changeUserJifen($iData['user_id'], $iData['jifen_cost'], false);
        });


        event(new JifenOrderCreateEvent($iData));

        return true;
    }

    /**
     * 获取兑换订单列表带份页
     *
     * @param array $where
     * @param array $select
     * @param array $orderBy
     * @param int $page
     * @param int $limit
     * @param bool $withPage
     * @return mixed
     */
    public function getOrderListWithPage($where = [], $select = ['*'], $orderBy = [], $page = 0, $limit = 0, $withPage = true)
    {
        $list = $this->dto->getOrderList($where, $select, $orderBy, $page, $limit, $withPage);

        return batch_set_oss_url($list, 'image');
    }

    /**
     * 获取兑换订单详情
     *
     * @param $orderNo
     * @return mixed
     */
    public function getOrderDetail($orderNo)
    {
        $detail = $this->dto->getOrderDetail($orderNo);
        $detail['image'] = set_oss_url($detail['image']);
        return $detail;
    }

}
