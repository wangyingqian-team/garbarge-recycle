<?php

namespace App\Http\Controllers\Admin;

use App\Services\GarbageRecycle\GarbageCategoryService;

class GarbageCategoryController extends BaseController
{
    /** @var GarbageCategoryService */
    private $service;

    public function init()
    {
        $this->service = app(GarbageCategoryService::class);
    }

    /**
     * 查询垃圾回收大类列表
     *
     * @return mixed
     *
     */
    public function getGarbageCategoryList()
    {
        $select = ['*'];
        $result = $this->service->getGarbageCategoryList([], $select);

        return $this->success($result);
    }

    /**
     * 查询垃圾分类大类详情
     *
     * @return mixed
     */
    public function getGarbageCategoryInfo()
    {
        $categoryId = $this->request->get('category_id');

        $result = $this->service->getGarbageCategoryInfo($categoryId);

        return $this->success($result);
    }

    /**
     * 添加垃圾回收大类
     *
     * @return int
     */
    public function addGarbageCategory()
    {
        $categoryName = $this->request->post('category_name');

        $result = $this->service->addGarbageCategory($categoryName);

        return $this->success($result);
    }

    /**
     * 修改垃圾回收大类名称
     *
     * @return mixed
     *
     */
    public function updateGarbageCategory()
    {
        $categoryId = $this->request->post('category_id');
        $categoryName = $this->request->post('category_name');

        $result = $this->service->updateGarbageCategory($categoryId, $categoryName);

        return $this->success($result);
    }

    /**
     * 删除垃圾回收大类
     *
     * @return mixed
     *
     */
    public function deleteGarbageCategory()
    {
        $categoryId = $this->request->get('category_id');

        $result = $this->service->deleteGarbageCategory($categoryId);

        return $this->success($result);
    }
}