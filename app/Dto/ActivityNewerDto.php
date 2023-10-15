<?php

namespace App\Dto;

class ActivityNewerDto extends Dto
{
    public function create($data)
    {
        $iData = [
            'type' => $data['type'],
            'total_count' => $data['total_count'],
            'jifen_count' => $data['jifen_count'],
        ];

        return $this->query->insert($iData);
    }

    public function update($id, $data)
    {
        $iData = [
            'type' => $data['type'],
            'total_count' => $data['total_count'],
            'jifen_count' => $data['jifen_count'],
        ];

        return $this->query->whereKey($id)->update(array_filter($iData));
    }

    public function getList()
    {
        return $this->query->groupBy('type')->get()->toArray();
    }

    public function getDetail($id)
    {
        return $this->query->whereKey($id)->macroFirst();
    }
}
