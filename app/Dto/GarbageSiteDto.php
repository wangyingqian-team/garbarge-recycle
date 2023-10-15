<?php

namespace App\Dto;

class GarbageSiteDto extends Dto
{
    public function create($data)
    {
        $val = [
            'admin_id' => $data['admin_id'],
            'name' => $data['name'],
            'mobile' => $data['mobile'],
            'province' => $data['province'],
            'city' => $data['city'],
            'area' => $data['area'],
            'address' => $data['address'],
            'is_work' => $data['is_work'],
            'work_time_slot' => json_encode($data['work_time_slot']),
            'is_throw' => $data['is_throw'],
            'is_recycle' => $data['is_recycle']
        ];

        return $this->query->insert($val);
    }

    public function update($id, $data)
    {
        $val = [
            'admin_id' => $data['admin_id'] ?? null,
            'name' => $data['name'] ?? null,
            'mobile' => $data['mobile'] ?? null,
            'province' => $data['province'] ?? null,
            'city' => $data['city'] ?? null,
            'area' => $data['area'] ?? null,
            'address' => $data['address'] ?? null,
            'is_work' => $data['is_work'] ?? null,
            'work_time_slot' => $data['work_time_slot'] ? json_encode($data['work_time_slot']) : null,
             'is_throw' => $data['is_throw'] ?? null,
            'is_recycle' => $data['is_recycle'] ?? null
        ];

        return $this->query->whereKey($id)->update(array_filter($val));
    }

    public function delete($id)
    {
        return $this->query->whereKey($id)->delete();
    }

    public function getList($where, $select, $orderBy, $page, $pageSize, $withPage)
    {
        return $this->query->macroQuery($where, $select, $orderBy, $page, $pageSize, $withPage);
    }

    public function getDetailById($id, $select)
    {
        return $this->query->macroWhere(['id' => $id])->macroSelect($select)->macroFirst();
    }

    public function getIdByAdminId($adminId)
    {
        $row = $this->query->whereKey($adminId)->macroFirst(['id']);

        return $row['id'] ?? 0;
    }
}
