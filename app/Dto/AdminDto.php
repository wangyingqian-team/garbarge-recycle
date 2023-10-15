<?php

namespace App\Dto;

class AdminDto extends Dto
{
    public function exist($name) {
        return $this->query->where('name', $name)->exists();
    }


    public function register($data) {
        $iData = [
            'name' => $data['name'],
            'password' => $data['password'],
            'status' => $data['status'],
            'role_id' => $data['role_id'],
        ];

        return $this->query->insert($iData);
    }

    public function getAdminInfoByName($name) {
        return $this->query->where('name', $name)->macroFirst();
    }
}
