<?php

namespace App\Http\Controllers\Admin;

use App\Services\GarbageRecycle\GarbageTypeService;

class GarbageTypeController extends BaseController
{
    /** @var GarbageTypeService */
    private $service;

    public function init()
    {
        $this->service = app(GarbageTypeService::class);
    }

    /**
     * 查询垃圾种类列表（通用）
     *
     * @return mixed
     */
    public function getGarbageTypeList()
    {
        $categoryId = $this->request->get('category_id');
        $categoryName = $this->request->get('category_name');
        $isPopular = $this->request->get('is_popular');
        $page = $this->request->get('page');
        $pageSize = $this->request->get('page_size');

        $where = [];
        !empty($categoryId) && $where['category_id'] = $categoryId;
        !empty($categoryName) && $where['category_name|like'] = $categoryName;
        !empty($isPopular) && $where['is_popular'] = $isPopular;

        $select = ['*'];
        $orderBy = ['create_time' => 'desc'];
        $withPage = true;

        $result = $this->service->getGarbageTypeList($where, $select, $orderBy, $page, $pageSize, $withPage);

        return $this->success($result);
    }

    /**
     * 查询垃圾种类详情
     *
     * @return mixed
     */
    public function getGarbageTypeInfo()
    {
        $typeId = $this->request->get('type_id');

        $result = $this->service->getGarbageTypeInfo($typeId);

        return $this->success($result);
    }

    /**
     * 添加垃圾种类
     *
     * @return mixed
     *
     */
    public function addGarbageType()
    {
        $categoryId = $this->request->post('category_id');
        $unitName = $this->request->post('unit_name');
        $isPopular = $this->request->post('is_popular');
        $typeName = $this->request->post('type_name');
        $icon = $this->request->post('icon');
        $recyclingPrice = $this->request->post('recycling_price');
        $sellingPrice = $this->request->post('selling_price');

        $garbageTypeData = [
            'category_id' => $categoryId,
            'unit_name' => $unitName,
            'is_popular' => $isPopular,
            'name' => $typeName,
            'icon' => $icon,
            'recycling_price' => $recyclingPrice,
            'selling_price' => $sellingPrice
        ];

        $result = $this->service->addGarbageType($garbageTypeData);

        return $this->success($result);
    }

    /**
     * 修改垃圾种类
     *
     * @return mixed
     *
     */
    public function updateGarbageType()
    {
        $garbageTypeId = $this->request->post('type_id');
        $categoryId = $this->request->post('category_id');
        $unitName = $this->request->post('unit_name');
        $isPopular = $this->request->post('is_popular');
        $typeName = $this->request->post('type_name');
        $icon = $this->request->post('icon');
        $recyclingPrice = $this->request->post('recycling_price');
        $sellingPrice = $this->request->post('selling_price');

        $garbageTypeData = [
            'category_id' => $categoryId,
            'unit_name' => $unitName,
            'is_popular' => $isPopular,
            'name' => $typeName,
            'icon' => $icon,
            'recycling_price' => $recyclingPrice,
            'selling_price' => $sellingPrice
        ];

        $result = $this->service->updateGarbageType($garbageTypeId, $garbageTypeData);

        return $this->success($result);
    }

    /**
     * 删除垃圾种类
     *
     * @return mixed
     *
     */
    public function deleteGarbageType()
    {
        $garbageTypeId = $this->request->get('type_id');

        $result = $this->service->deleteGarbageType($garbageTypeId);

        return $this->success($result);
    }
}