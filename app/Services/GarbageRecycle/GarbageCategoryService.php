<?php

namespace App\Services\GarbageRecycle;

use App\Dto\GarbageCategoryDto;

class GarbageCategoryService
{
    /** @var GarbageCategoryDto */
    public $dto;

    public function __construct()
    {
        $this->dto = app(GarbageCategoryDto::class);
    }

    /**
     * 查询垃圾回收大类列表
     *
     * @param array $filters
     * @param array $select
     * @return mixed
     *
     */
    public function getGarbageCategoryList($filters, $select)
    {
        return $this->dto->getGarbageCategoryList($filters, $select, ['id' => 'asc']);
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
        return $this->dto->getGarbageCategoryInfo($categoryId);
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
        return $this->dto->addGarbageCategory($categoryName);
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
        return $this->dto->updateGarbageCategory($categoryId, $categoryName);
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
        return $this->dto->deleteGarbageCategory($categoryId);
    }
}