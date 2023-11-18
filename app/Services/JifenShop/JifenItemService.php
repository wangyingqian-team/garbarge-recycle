<?php

namespace App\Services\JifenShop;

use App\Models\JifenItemModel;

class JifenItemService
{
    /**
     * 添加商品.
     *
     * @param $title string
     * @param $image string
     * @param $jifen double
     * @return int
     */
    public function createItem($title, $image, $jifen)
    {
        return JifenItemModel::query()->insertGetId([
            'title' => $title,
            'primary_image' => $image,
            'jifen_need' => $jifen
        ]);
    }

    /**
     * 修改商品.
     *
     * @param $id string
     * @param $data array
     * @return bool
     */
    public function update($id, $data)
    {
        $updateData = [];
        if (!empty($data['title'])) {
            $updateData['title'] = $data['title'];
        }
        if (!empty($data['image'])) {
            $updateData['primary_image'] = $data['image'];
        }
        if (!empty($data['jifen'])) {
            $updateData['jifen_need'] = $data['jifen'];
        }

        if (!empty($updateData)) {
            JifenItemModel::query()->whereKey($id)->update($updateData);
        }

        return true;
    }

    /**
     * 删除商品.
     *
     * @param $id int
     * @return mixed
     */
    public function delete($id) {
        return JifenItemModel::query()->whereKey($id)->delete();
    }


    /**
     * 积分商品列表
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
        return JifenItemModel::query()->macroQuery($where, $select, $orderBy, $page, $limit, $withPage);
    }

    /**
     * 单个积分商品详情.
     *
     * @param $itemId
     * @return mixed
     */
    public function getItemInfo($itemId)
    {
        return JifenItemModel::query()->whereKey($itemId)->macroFirst();
    }

}
