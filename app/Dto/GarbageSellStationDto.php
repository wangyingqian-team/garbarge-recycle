<?php

namespace App\Dto;

use App\Models\GarbageSellStationModel;
use App\Supports\Macro\Builder;

class GarbageSellStationDto extends Dto
{
    public $model = GarbageSellStationModel::class;

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
    public function getGarbageSellStationList($where, $select, $orderBy, $page = 0, $limit = Builder::PER_LIMIT, $withPage = false)
    {
        return $this->query->macroQuery($where, $select, $orderBy, $page, $limit, $withPage);
    }

    /**
     * 查询垃圾售卖站详细信息
     *
     * @param int $garbageSellStationId
     *
     * @return mixed
     *
     */
    public function getGarbageSellStationInfo($garbageSellStationId)
    {
        return $this->query->whereKey($garbageSellStationId)->macroFirst();
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
        return $this->query->insertGetId([
            'name' => $garbageSellStationData['station_name'],
            'address' => $garbageSellStationData['station_address'],
            'contacts' => $garbageSellStationData['station_contacts'],
            'mobile' => $garbageSellStationData['station_mobile']
        ]);
    }

    /**
     * 修改垃圾售卖站
     *
     * @param int $garbageSellStationId
     * @param array $garbageSellStationData
     *
     * @return int
     *
     */
    public function updateGarbageSellStation($garbageSellStationId, $garbageSellStationData)
    {
        return $this->query->whereKey($garbageSellStationId)->update($garbageSellStationData);
    }

    /**
     * 删除垃圾售卖站
     *
     * @param int $garbageSellStationId
     *
     * @return mixed
     *
     */
    public function deleteGarbageSellStation($garbageSellStationId)
    {
        return $this->query->whereKey($garbageSellStationId)->delete();
    }
}