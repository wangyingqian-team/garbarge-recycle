<?php

namespace App\Services\User;

use App\Models\VillageModel;

class VillageService
{
    /**
     * @param $data
     * @return bool
     * @throws \Throwable
     */
    public function createVillage($data)
    {
        VillageModel::query()->insert([
            'name' => $data['name'],
            'province' => $data['province'],
            'city' => $data['city'],
            'area' => $data['area'],
            'is_active' => $data['is_active'],
            'type' => $data['type']
        ]);

        return true;
    }

    /**
     * 获取小区详细信息.
     *
     * @param $villageId int 小区ID
     * @return mixed
     */
    public function getVillageInfo($villageId)
    {
        return VillageModel::query()->whereKey($villageId)->macroFirst();
    }

    /**
     * @param $id
     * @param $data
     * @throws \Throwable
     */
    public function updateVillage($id, $data)
    {
        VillageModel::query()->whereKey($id)->update([
            'name' => $data['name'],
            'is_active' => $data['is_active'],
            'type' => $data['type']
        ]);

        return true;
    }

    /**
     * 通过地区和小区名搜索小区列表
     *
     * @param $area
     * @param string $name
     * @return array
     */
    public function getVillageByArea($area, $name = '')
    {
        $where['area'] = $area;
        if (!empty($name)) {
            $where['name'] = '%' . $name . '%';
        }
        return VillageModel::query()->where($where)->get()->toArray();
    }


    public function getVillageList(
        $where = [],
        $select = ['*'],
        $page = 0,
        $pageSize = 0,
        $withPage = false
    )
    {
        return VillageModel::query()->macroQuery($where, $select, [], $page, $pageSize, $withPage);
    }
}
