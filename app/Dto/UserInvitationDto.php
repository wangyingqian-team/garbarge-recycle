<?php

namespace App\Dto;

class UserInvitationDto extends Dto
{
    public function create($inviteId, $userId)
    {
        $this->query->create(
            [
                'user_id' => $inviteId,
                'new_user_id' => $userId
            ]
        );

        return true;
    }

    public function getTotalByUserId($userId) {
        return $this->query->where('user_id', $userId)->count();
    }
}
