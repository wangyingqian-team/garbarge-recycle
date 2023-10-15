<?php

namespace App\Services\GarbageRecycle;

use App\Dto\GarbageSellStationDto;

class GarbageSellStationService
{
    /** @var GarbageSellStationDto */
    private $dto;

    public function __construct()
    {
        $this->dto = app(GarbageSellStationDto::class);
    }

    /**
     * 查询垃圾售卖站列表
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
    public function getGarbageSellStationList($where, $select, $orderBy, $page, $limit, $withPage)
    {
        return $this->dto->getGarbageSellStationList($where, $select, $orderBy, $page, $limit, $withPage);
    }

    /**
     * 查询垃圾售卖站详细信息
     *
     * @param int $stationId
     *
     * @return mixed
     *
     */
    public function getGarbageSellStationInfo($stationId)
    {
        return $this->dto->getGarbageSellStationInfo($stationId);
    }

    /**
     * 添加垃圾售卖站
     *
     * @param array $garbageSellStationData
     *
     * @return int
     *
     */
    public function addGarbageSellStation($garbageSellStationData)
    {
        return $this->dto->addGarbageSellStation($garbageSellStationData);
    }

    /**
     * 修改垃圾售卖站
     *
     * @param int $stationId
     * @param array $stationData
     *
     * @return int
     *
     */
    public function updateGarbageSellStation($stationId, $stationData)
    {
        return $this->dto->updateGarbageSellStation($stationId, array_filter($stationData));
    }

    /**
     * 删除垃圾售卖站
     *
     * @param int $stationId
     *
     * @return mixed
     *
     */
    public function deleteGarbageSellStation($stationId)
    {
        return $this->dto->deleteGarbageSellStation($stationId);
    }
}