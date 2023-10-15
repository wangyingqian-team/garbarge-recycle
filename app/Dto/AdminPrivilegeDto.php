<?php

namespace App\Dto;

class AdminPrivilegeDto extends Dto
{
    public function insertOrUpdate($data) {
        $attr = [
            'role_id' => $data['role_id']
        ];
        $val = [
            'privilege' => $data['privilege']
        ];

        return $this->query->updateOrInsert($attr, $val);
    }

    public function getPrivilege($roleId) {
        return $this->query->where('role_id', $roleId)->macroFirst('privilege');
    }
}
