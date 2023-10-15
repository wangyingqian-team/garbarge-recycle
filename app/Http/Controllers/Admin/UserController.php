<?php

namespace App\Http\Controllers\Admin;


use App\Services\User\UserAddressService;
use App\Services\User\UserService;
use App\Services\Village\VillageService;


class UserController extends BaseController
{
    public function getUserList()
    {
        $where = [];
        if ($this->siteId) {
            //过滤掉其他用户
            $villageIds = app(VillageService::class)->getVillageList(
                ['site_id' => $this->siteId],
                ['id']
            );
            $data = app(UserAddressService::class)->getAddressByVillageIds($villageIds);
            $userIds = collect($data)->pluck('user_id')->unique()->toArray();

            $where['id|in'] = $userIds;
        }

        $res = app(UserService::class)->getUserList(
            $where,
            ['*', 'assets.*'],
            ['create_time' => 'desc'],
            $this->page,
            $this->pageSize
        );

        return $this->success($res);
    }

    public function getUserAddress()
    {
        $userId = $this->request->get('user_id');

        $address = app(UserAddressService::class)->getAddressListByUserId($userId);
        $data = collect($address)->sortBy('is_default')->toArray();

        return $this->success($data);
    }

}
