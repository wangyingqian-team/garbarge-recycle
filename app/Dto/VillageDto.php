<?php

namespace App\Dto;

class VillageDto extends Dto
{
    public function create($data)
    {
        $iData = [
            'name' => $data['name'],
            'site_id' => $data['site_id'],
            'is_throw' => $data['is_throw'],
            'is_recycle' => $data['is_recycle'],
        ];

        return $this->query->insertGetId($iData);
    }

    public function update($id, $data)
    {
        $iData = [
            'name' => $data['name'] ?? null,
            'site_id' => $data['site_id'] ?? null,
            'is_throw' => $data['is_throw'] ?? null,
            'is_recycle' => $data['is_recycle'] ?? null,
        ];

        $iData = array_null($iData);

        if (!empty($iData)) {
            $this->query->whereKey($id)->update($iData);
        }

        return true;
    }

    public function delete($id) {
        return $this->query->whereKey($id)->delete();
    }

    public function getList($where, $select, $orderBy = [], $page = 0, $pageSize = 0, $withPage = true)
    {
        return $this->query->macroQuery($where, $select, $orderBy, $page, $pageSize, $withPage);
    }

    public function getDetail($id, $select = ['*'])
    {
        return $this->query->macroWhere(['id' => $id])->macroSelect($select)->macroFirst();
    }

}
