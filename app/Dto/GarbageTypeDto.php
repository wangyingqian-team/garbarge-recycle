<?php

namespace App\Dto;

use App\Models\GarbageTypeModel;
use App\Supports\Macro\Builder;

class GarbageTypeDto extends Dto
{
    public $model = GarbageTypeModel::class;

    /**
     * 查询垃圾种类列表（通用）
     *
     * @param array $where
     * @param array $select
     * @param array $orderBy
     * @param int $page
     * @param int $limit
     * @param boolean $withPage
     *
     * @return mixed
     */
    public function getGarbageTypeList($where, $select, $orderBy, $page = 1, $limit = Builder::PER_LIMIT, $withPage = false)
    {
        return $this->query->macroQuery($where, $select, $orderBy, $page, $limit, $withPage);
    }

    /**
     * 查询垃圾种类详情
     *
     * @param int $typeId
     *
     * @return mixed
     */
    public function getGarbageTypeInfo($typeId)
    {
        return $this->query->whereKey($typeId)->macroFirst();
    }

    /**
     * 添加垃圾种类
     *
     * @param array $garbageTypeData
     *
     * @return int
     *
     */
    public function addGarbageType($garbageTypeData)
    {
        return $this->query->insertGetId([
            'category_id' => $garbageTypeData['category_id'],
            'unit_name' => $garbageTypeData['unit_name'],
            'is_popular' => $garbageTypeData['is_popular'],
            'name' => $garbageTypeData['name'],
            'icon' => $garbageTypeData['icon'],
            'recycling_price' => $garbageTypeData['recycling_price'],
            'selling_price' => $garbageTypeData['selling_price']
        ]);
    }

    /**
     * 修改垃圾种类
     *
     * @param int $garbageTypeId
     * @param array $garbageTypeData
     *
     * @return int
     *
     */
    public function updateGarbageType($garbageTypeId, $garbageTypeData)
    {
        return $this->query->whereKey($garbageTypeId)->update($garbageTypeData);
    }

    /**
     * 删除垃圾种类
     *
     * @param int $garbageTypeId
     *
     * @return mixed
     *
     */
    public function deleteGarbageType($garbageTypeId)
    {
        return $this->query->whereKey($garbageTypeId)->delete();
    }
}