<?php

namespace App\Dto;

class UserDto extends Dto
{
    /**
     * 新增用户
     *
     * @param $data
     * @return bool
     */
    public function create($data)
    {
        $val = [
            'openid' => $data['openid'],
            'nickname' => $data['nickname'],
            'sex' => $data['sex'],
            'avatar' => $data['headimgurl'],
        ];

        return $this->query->insertGetId($val);
    }

    public function getUserList($where, $select, $orderBy, $page = 0, $limit = 0, $withPage = false)
    {
        return $this->query->macroQuery($where, $select, $orderBy, $page, $limit, $withPage);
    }


    public function getUserByOpenid($openid)
    {
        return $this->query->where('openid', $openid)->macroFirst();
    }

    public function getUserByUserId($userId)
    {
        return $this->query->where('id', $userId)->macroFirst();
    }

    public function updateNewerEndTimeByUserId($userId, $time)
    {
        return $this->query->whereKey($userId)->update(['newer_end_time' => $time]);
    }

    public function update($userId, $data)
    {
        $val = [
            'nickname' => $data['nickname'] ?? null,
            'avatar' => $data['headimgurl'] ?? null,
            'mobile' => $data['mobile'] ?? null,
            'is_recycler' => $data['is_recycler'] ?? null,
            'is_admin' => $data['is_admin'] ?? null
        ];

        $val = array_null($val);
        if (!empty($val)) {
            $this->query->whereKey($userId)->update($val);
        }
        return true;
    }
}
