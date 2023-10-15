<?php

namespace App\Dto;

class ActivityInvitationDto extends Dto
{
    public function create($data)
    {
        $iData = [
            'invite_user_count' => $data['invite_user_count'],
            'bean_count' => $data['bean_count'],
        ];

        return $this->query->insert($iData);
    }

    public function update($id, $data)
    {
        $iData = [
            'invite_user_count' => $data['invite_user_count'] ?? null,
            'bean_count' => $data['bean_count'] ?? null,
        ];

        return $this->query->whereKey($id)->update(array_filter($iData));
    }

    public function getList()
    {
        return $this->query->get()->toArray();
    }

    public function getDetail($id)
    {
        return $this->query->whereKey($id)->macroFirst();
    }
}
