<?php

namespace App\Dto;

class JifenItemDto extends Dto
{
    public function getItemList($where, $select = ['*'], $orderBy = [], $page = 0, $limit = 0, $withPage = true)
    {
        return $this->query->macroQuery($where, $select, $orderBy, $page, $limit, $withPage);
    }

    public function getItemInfoById($itemId) {
        return $this->query->where('id', $itemId)->macroFirst();
    }

    public function create($data) {
        $val = [
            'title' => $data['title'],
            'primary_image' => $data['primary_image'],
            'jifen_need' => $data['jifen_need'],
            'unit_name' => $data['unit_name']
        ];
        return $this->query->insert($val);
    }

    public function update($id ,$data) {
        $val = [
            'title' => $data['title'] ??null,
            'primary_image' => $data['primary_image'] ?? null,
            'jifen_need' => $data['jifen_need'] ?? null,
            'unit_name' => $data['unit_name'] ?? null
        ];

        $val = array_null($val);
        if (!empty($val)) {
            return $this->query->whereKey($id)->update($data);
        }

        return true;
    }

    public function delete($id) {
        return $this->query->whereKey($id)->delete();
    }
}
