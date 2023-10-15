<?php

namespace App\Dto;

class UserActivityLogDto extends Dto
{
    public function create($userId, $activityId, $type)
    {
        $val = [
            'user_id' => $userId,
            'activity_id' => $activityId,
            'type' => $type
        ];

        return $this->query->insert($val);
    }

    public function getListByUserId($userId, $type = null)
    {
        $where['user_id'] = $userId;
        $type && $where['type'] = $type;
        return $this->query->where($where)->get()->toArray();
    }

    public function hasReceived($userId, $activityId, $type)
    {
        $where = [
            'user_id' => $userId,
            'activity_id' => $activityId,
            'type' => $type
        ];
        return $this->query->where($where)->exists();
    }
}
