<?php

namespace App\Http\Controllers\Admin;

use App\Services\GarbageRecycle\GarbageSiteService;

class GarbageSiteStationController extends BaseController
{
    /** @var GarbageSiteService */
    private $service;

    public function init()
    {
        parent::init();
        $this->service = app(GarbageSiteService::class);
    }

    public function getGarbageSiteList()
    {
        $name = $this->request->get('name');

        $where = [];
        !empty($name) && $where['name|like'] = $name;


        $select = ['*','admin.*'];
        $orderBy = ['create_time' => 'desc'];

        $result = $this->service->getGarbageSiteList($where, $select, $orderBy, $this->page, $this->pageSize);

        return $this->success($result);
    }

    public function getGarbageSiteDetail()
    {
        $stationId = $this->request->get('id');

        $result = $this->service->getGarbageSiteDetail($stationId, ['*', 'admin.*']);

        return $this->success($result);
    }

    public function createGarbageSite()
    {
        $data = [
            'admin_id' => $this->adminId,
            'name' => $this->request->post('name'),
            'mobile' => $this->request->post('mobile'),
            'province' => $this->request->post('province'),
            'city' => $this->request->post('city'),
            'area' => $this->request->post('area'),
            'address' => $this->request->post('address'),
            'is_work' => $this->request->post('is_work'),
            'work_time_slot' => $this->request->post('work_time_slot'),
            'is_throw' => $this->request->post('is_throw'),
            'is_recycle' => $this->request->post('is_recycle'),
        ];
        $result = $this->service->createGarbageSite($data);

        return $this->success($result);
    }

    public function updateGarbageSite()
    {
        $id = $this->request->get('id');
        $data = [
            'admin_id' => $this->adminId,
            'name' => $this->request->get('name'),
            'mobile' => $this->request->get('mobile'),
            'province' => $this->request->get('province'),
            'city' => $this->request->get('city'),
            'area' => $this->request->get('area'),
            'address' => $this->request->get('address'),
            'is_work' => $this->request->get('is_work'),
            'work_time_slot' => $this->request->get('work_time_slot'),
            'is_throw' => $this->request->get('is_throw'),
            'is_recycle' => $this->request->get('is_recycle'),
        ];
        if ($iData = array_null($data)) {
            $this->service->updateGarbageSite($id, $iData);
        }

        return $this->success();
    }


    public function deleteGarbageSite()
    {
        $stationId = $this->request->get('id');

        $result = $this->service->deleteGarbageSite($stationId);

        return $this->success($result);
    }
}
