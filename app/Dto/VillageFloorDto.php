<?php

namespace App\Dto;

class VillageFloorDto extends Dto
{
    public function create($id, $data)
    {
        foreach ($data as $floor) {
            $iData[] = [
                'village_id' => $id,
                'floor' => $floor . '幢',
            ];
        }

        return $this->query->insert($iData);
    }


    public function delete($ids)
    {
        return $this->query->whereIn('id', $ids)->delete();
    }

    public function deleteByVillageId($villageId)
    {
        return $this->query->where('village_id', $villageId)->delete();
    }

    public function getFloorDetail($id)
    {
        return $this->query->whereKey($id)->macroFirst();
    }

    /**
     * 获取小区楼栋列表
     *
     * @param array $where
     * @param array $select
     * @param array $orderBy
     * @param int $page
     * @param int $pageSize
     * @param bool $withPage
     *
     * @return mixed
     *
     */
    public function getFloorList($where, $select, $orderBy = [], $page = 0, $pageSize = 0, $withPage = false)
    {
        return $this->query->macroQuery($where, $select, $orderBy, $page, $pageSize, $withPage);
    }

}
