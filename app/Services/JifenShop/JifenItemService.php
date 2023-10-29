<?php

namespace App\Services\JifenShop;

use App\Dto\JifenItemDto;
use App\Exceptions\RestfulException;

class JifenItemService
{
    /** @var JifenItemDto */
    public $dto;

    public function __construct()
    {
        $this->dto = app(JifenItemDto::class);
    }

    public function create($data)
    {
        $data['primary_image'] = get_oss_url($data['primary_image']);
        return $this->dto->create($data);
    }

    public function update($id, $data)
    {
        if (isset($data['primary_image']) && !empty($data['primary_image'])) {
            $data['primary_image'] = get_oss_url($data['primary_image']);
        }

        return $this->dto->update($id, $data);
    }

    public function delete($id) {
        return $this->dto->delete($id);
    }

    /**
     * 获取指定个数积分商品
     *
     * @param $where
     * @param $limit
     * @return mixed
     */
    public function getAppointList($limit)
    {
        $list = $this->dto->getItemList([], ['*'], [], 1, $limit, false);
        return batch_set_oss_url($list, 'primary_image');
    }


    /**
     * 列表
     *
     * @param array $where
     * @param array $select
     * @param array $orderBy
     * @param int $page
     * @param int $limit
     * @param bool $withPage
     * @return mixed
     */
    public function getItemList($where = [], $select = ['*'], $orderBy = [], $page = 0, $limit = 0, $withPage = true)
    {
        $list = $this->dto->getItemList($where, $select, $orderBy, $page, $limit, $withPage);

        return batch_set_oss_url($list, 'primary_image');
    }

    public function getItemInfoById($itemId, $image = true)
    {
        $item = $this->dto->getItemInfoById($itemId);

        $image && $item['primary_image'] = set_oss_url($item['primary_image']);

        return $item;
    }

}
