<?php

namespace App\Services\GarbageRecycle;

use App\Dto\GarbageTypeDto;
use App\Exceptions\RestfulException;
use App\Supports\Constant\GarbageRecycleConst;

class GarbageTypeService
{
    /** @var GarbageTypeDto */
    public $dto;

    public function __construct()
    {
        $this->dto = app(GarbageTypeDto::class);
    }

    /**
     * 查询指定垃圾分类大类下的垃圾种类列表
     *
     * @param int $categoryId
     * @param array $select
     * @param array $orderBy
     * @param int $page
     * @param int $limit
     * @param boolean $withPage
     *
     * @return mixed
     */
    public function getGarbageTypeListByCategoryId($categoryId, $select, $orderBy, $page, $limit, $withPage)
    {
        $where = [
            'category_id' => $categoryId
        ];

        $garbageTypeList = $this->dto->getGarbageTypeList($where, $select, $orderBy, $page, $limit, $withPage);

        // 图片处理
        if (!empty($garbageTypeList['items'])) {
            $garbageTypeList['items'] = batch_set_oss_url($garbageTypeList['items'], 'icon');
        }

        return $garbageTypeList;
    }


    /**
     * 查询常用垃圾种类列表
     *
     * @param array $select
     *
     * @return mixed
     */
    public function getPopularGarbageTypeList($select)
    {
        $where = [
            'is_popular' => GarbageRecycleConst::GARBAGE_RECYCLE_TYPE_POPULAR
        ];
        $orderBy = ['id' => 'asc'];


        $garbageTypeList = $this->dto->getGarbageTypeList($where, $select, $orderBy);

        // 图片处理
        if (!empty($garbageTypeList['items'])) {
            $garbageTypeList['items'] = batch_set_oss_url($garbageTypeList['items'], 'icon');
        }

        return $garbageTypeList;
    }

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
    public function getGarbageTypeList($where, $select, $orderBy, $page, $limit, $withPage)
    {
        $garbageTypeList = $this->dto->getGarbageTypeList($where, $select, $orderBy, $page, $limit, $withPage);

        // 图片处理
        if (!empty($garbageTypeList['items'])) {
            $garbageTypeList['items'] = batch_set_oss_url($garbageTypeList['items'], 'icon');
        }

        return $garbageTypeList;
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
        $garbageTypeList = $this->dto->getGarbageTypeInfo($typeId);

        // 图片处理
        if (!empty($garbageTypeList['items'])) {
            $garbageTypeList['items'] = batch_set_oss_url($garbageTypeList['items'], 'icon');
        }

        return $garbageTypeList;
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
        if (!in_array($garbageTypeData['is_popular'], GarbageRecycleConst::GARBAGE_RECYCLE_TYPES)) {
            throw new RestfulException("垃圾种类的常用状态指定不正确！");
        }

        if (!empty($garbageTypeData['icon'])) {
            $garbageTypeData['icon'] = get_oss_url($garbageTypeData['icon']);
        }

        return $this->dto->addGarbageType($garbageTypeData);
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
        if (isset($garbageTypeData['is_popular']) && !in_array($garbageTypeData['is_popular'], GarbageRecycleConst::GARBAGE_RECYCLE_TYPES)) {
            throw new RestfulException("垃圾种类的常用状态指定不正确！");
        }

        if (!empty($garbageTypeData['icon'])) {
            $garbageTypeData['icon'] = get_oss_url($garbageTypeData['icon']);
        }

        // 价格更新时间处理.
        if (!empty($garbageTypeData['recycling_price'])) {
            $garbageTypeData['recycling_price_update_time'] = date("Y-m-d H:i:s", now());
        }

        if (!empty($garbageTypeData['selling_price'])) {
            $garbageTypeData['recycling_price_update_time'] = date("Y-m-d H:i:s", now());
        }

        return $this->dto->updateGarbageType($garbageTypeId, array_filter($garbageTypeData));
    }

    /**
     * 删除垃圾种类
     *
     * @param int $garbageTypeId
     *
     * @return int
     *
     */
    public function deleteGarbageType($garbageTypeId)
    {
        return $this->dto->deleteGarbageType($garbageTypeId);
    }

}