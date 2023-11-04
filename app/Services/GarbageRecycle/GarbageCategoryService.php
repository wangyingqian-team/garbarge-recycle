<?php

namespace App\Services\GarbageRecycle;

use App\Models\GarbageCategoryModel;
use App\Supports\Macro\Builder;

class GarbageCategoryService
{
    /**
     * 查询垃圾回收大类列表
     *
     * @param array $where
     * @param array $select
     * @param array $orderBy
     * @param int $page
     * @param int $limit
     * @param boolean $withPage
     *
     * @return mixed
     *
     */
    public function getGarbageCategoryList($where, $select, $orderBy, $page = 0, $limit = Builder::PER_LIMIT, $withPage = false)
    {
        return GarbageCategoryModel::query()->macroQuery($where, $select, $orderBy, $page, $limit);
    }

    /**
     * 查询垃圾分类大类详情
     *
     * @param string $categoryId
     *
     * @return mixed
     */
    public function getGarbageCategoryInfo($categoryId)
    {
        return GarbageCategoryModel::query()->whereKey($categoryId)->macroFirst();
    }

    /**
     * 添加垃圾回收大类
     *
     * @param string $categoryName
     *
     * @return int
     */
    public function addGarbageCategory($categoryName)
    {
        return GarbageCategoryModel::query()->insertGetId([
            'name' => $categoryName
        ]);
    }

    /**
     * 修改垃圾回收大类名称
     *
     * @param int $categoryId
     * @param string $categoryName
     *
     * @return int
     *
     */
    public function updateGarbageCategory($categoryId, $categoryName)
    {
        return GarbageCategoryModel::query()->whereKey($categoryId)->update([
            'name' => $categoryName
        ]);
    }

    /**
     * 删除垃圾回收大类
     *
     * @param int $categoryId
     *
     * @return int
     *
     */
    public function deleteGarbageCategory($categoryId)
    {
        return GarbageCategoryModel::query()->whereKey($categoryId)->delete();
    }
}
