<?php

namespace App\Dto;

class UserSignLogDto extends Dto
{
    public function sign($userId)
    {
        $this->query->insert(
            [
                'user_id' => $userId
            ]
        );
    }
}
