<?php

namespace App\Dto;

class RecyclerDto extends Dto
{
    /**
     * 新增回收员
     *
     * @param $data
     * @return bool
     */
    public function create($data) {
        $val = [
            'user_id' => $data['user_id'],
            'site_id' => $data['site_id'],
            'real_name' => $data['real_name'],
            'mobile' => $data['mobile'],
            'status' =>$data['status'],
            'id_number' => $data['id_number'],
            'front_image' =>$data['front_image'],
            'back_image' => $data['back_image'],
        ];

        return $this->query->insertGetId($val);

    }

    public function update($id, $data) {
        $val = [
            'site_id' => $data['site_id'] ?? null,
            'real_name' => $data['real_name'] ?? null,
            'mobile' => $data['mobile'] ?? null,
            'status' =>$data['status'] ?? null,
            'id_number' => $data['id_number'] ?? null,
            'front_image' =>$data['front_image'] ?? null,
            'back_image' => $data['back_image'] ?? null,
        ];
        $val = array_null($val);

        if (!empty($val)) {
            $this->query->whereKey($id)->update($val);
        }

        return true;
    }

    /**
     * 查询回收员列表
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
    public function getList($where, $select = ['*'], $orderBy = [], $page = 0, $limit = 0, $withPage = false)
    {
        return $this->query->macroQuery($where, $select, $orderBy, $page, $limit, $withPage);
    }

    /**
     * 获取回收员详情
     *
     * @param $where
     * @param array $select
     * @return mixed
     */
    public function getDetail($where, $select = ['*']) {
        return $this->query->macroQuery($where, $select);
    }
}
