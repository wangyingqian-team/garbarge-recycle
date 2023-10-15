<?php

namespace App\Http\Controllers\Admin;

use App\Services\GarbageRecycle\GarbageSellStationService;

class GarbageSellStationController extends BaseController
{
    /** @var GarbageSellStationService */
    private $service;

    public function init()
    {
        $this->service = app(GarbageSellStationService::class);
    }

    /**
     * 获取垃圾售卖站列表
     *
     * @return mixed
     *
     */
    public function getGarbageSellStationList()
    {
        $stationName = $this->request->get('station_name');
        $stationMobile = $this->request->get('station_mobile');
        $page = $this->request->get('page');
        $pageSize = $this->request->get('page_size');

        $where = [];
        !empty($stationName) && $where['name|like'] = $stationName;
        !empty($stationMobile) && $where['mobile'] = $stationMobile;

        $select = ['*'];
        $orderBy = ['create_time' => 'desc'];
        $withPage = true;

        $result = $this->service->getGarbageSellStationList($where, $select, $orderBy, $page, $pageSize, $withPage);

        return $this->success($result);
    }

    /**
     * 获取垃圾售卖站详情
     *
     * @return mixed
     *
     */
    public function getGarbageSellStationInfo()
    {
        $stationId = $this->request->get('station_id');

        $result = $this->service->getGarbageSellStationInfo($stationId);

        return $this->success($result);
    }

    /**
     * 添加垃圾售卖站
     *
     * @return mixed
     *
     */
    public function addGarbageSellStation()
    {
        $stationName = $this->request->post('station_name');
        $stationAddress = $this->request->post('station_address');
        $stationContacts = $this->request->post('station_contacts');
        $stationMobile = $this->request->post('station_mobile');

        $garbageSellStationData = [
            'station_name' => $stationName,
            'station_address' => $stationAddress,
            'station_contacts' => $stationContacts,
            'station_mobile' => $stationMobile
        ];

        $result = $this->service->addGarbageSellStation($garbageSellStationData);

        return $this->success($result);
    }

    /**
     * 修改垃圾售卖站
     *
     * @return mixed
     *
     */
    public function updateGarbageSellStation()
    {
        $stationId = $this->request->post('station_id');
        $stationName = $this->request->post('station_name');
        $stationAddress = $this->request->post('station_address');
        $stationContacts = $this->request->post('station_contacts');
        $stationMobile = $this->request->post('station_mobile');

        $result = $this->service->updateGarbageSellStation($stationId, [
            'name' => $stationName,
            'address' => $stationAddress,
            'contacts' => $stationContacts,
            'mobile' => $stationMobile
        ]);

        return $this->success($result);
    }

    /**
     * 删除垃圾售卖站
     *
     * @return mixed
     *
     */
    public function deleteGarbageSellStation()
    {
        $stationId = $this->request->get('station_id');

        $result = $this->service->deleteGarbageSellStation($stationId);

        return $this->success($result);
    }
}