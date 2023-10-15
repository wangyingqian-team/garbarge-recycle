<?php

namespace App\Http\Controllers\Admin;


use App\Services\Coupon\ThrowCouponService;
use App\Services\Village\VillageService;

class VillageController extends BaseController
{

    /**
     * @return mixed
     * @throws \Throwable
     */
    public function createVillage()
    {
        $data = [
            'name' => $this->request->get('name'),
            'site_id' => $this->request->get('site_id'),
            'is_throw' => $this->request->get('is_throw'),
            'is_recycle' => $this->request->get('is_recycle'),
            'floors' => $this->request->get('floors')
        ];

        app(VillageService::class)->createVillage($data);

        return $this->success();
    }

    /**
     * @return mixed
     * @throws \Throwable
     */
    public function updateVillage()
    {
        $id = $this->request->get('id');
        $data = [
            'name' => $this->request->get('name'),
            'site_id' => $this->request->get('site_id'),
            'is_throw' => $this->request->get('is_throw'),
            'is_recycle' => $this->request->get('is_recycle'),
            'floors' => $this->request->get('floors')
        ];

        app(VillageService::class)->updateVillage($id, $data);

        return $this->success();
    }

    public function deleteVillage() {
        $id = $this->request->get('id');

        app(VillageService::class)->deleteVillage($id);

        return $this->success();
    }
    /**
     * 获取小区列表
     *
     * @return mixed
     */
    public function getVillageList()
    {
        $name = $this->request->get('name');

        $where = [
            'site_id' => $this->siteId
        ];
        $name && $where['name|like'] = $name;

        $data = app(VillageService::class)->getVillageList(
            $where,
            ['*'],
            ['create_time' => 'desc'],
            $this->page,
            $this->pageSize
        );

        return $this->success($data);
    }

    /**
     * 获取小区详情
     *
     * @return mixed
     */
    public function getVillageDetail()
    {
        $villageId = $this->request->get('village_id');
        $data = app(VillageService::class)->getVillageDetail($villageId, ['*', 'floor.*']);
        return $this->success($data);
    }
}
