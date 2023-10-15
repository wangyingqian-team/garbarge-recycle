<?php

namespace App\Services\Admin;

use App\Dto\AdminDto;
use App\Dto\AdminPrivilegeDto;
use App\Exceptions\RestfulException;
use App\Supports\Constant\AdminConst;
use App\Supports\Constant\RedisKeyConst;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redis;


class AdminService
{

    /** @var AdminDto */
    public $dto;

    public function __construct()
    {
        $this->dto = app(AdminDto::class);
    }

    public function register($data)
    {
        $exist = $this->dto->exist($data['name']);

        if ($exist) {
            throw new RestfulException('账号已存在');
        }

        $data['password'] = md5(AdminConst::ADMIN_SALT . $data['password']);

        return $this->dto->register($data);
    }

    public function login($name, $password)
    {
        $info = $this->dto->getAdminInfoByName($name);
        if ($info['status'] != AdminConst::LOGIN_ABLE) {
            throw new RestfulException('账号被禁用');
        }

        $pwd = md5(AdminConst::ADMIN_SALT . $password);
        if ($pwd != Arr::pull($info, 'password')) {
            throw new RestfulException('密码错误');
        }

        $info['token'] = md5(AdminConst::TOKEN_TIME . $info['id']);

        Redis::connection('admin')->setex(RedisKeyConst::ADMIN_TOKEN.':'.$info['id'], AdminConst::TOKEN_TIME, $info['token']);

        return $info;
    }

    public function setPrivilege($roleId, $privilege)
    {
        $privilege = json_encode($privilege);
        app(AdminPrivilegeDto::class)->insertOrUpdate(['role_id' => $roleId, 'privilege' => $privilege]);
        Redis::connection('admin')->hset(RedisKeyConst::ADMIN_ROLE_PRIVILEGE, $roleId, $privilege);
    }

    public function getPrivilege($roleId)
    {
        $privilege = Redis::connection('admin')->hget(RedisKeyConst::ADMIN_ROLE_PRIVILEGE, $roleId);
        if (empty($privilege)) {
            $privilege = app(AdminPrivilegeDto::class)->getPrivilege($roleId);
            if (empty($privilege)) {
                throw new RestfulException('未分配角色权限!', 2000);
            }
            $privilege = $privilege['privilege'];
            Redis::connection('admin')->hset(RedisKeyConst::ADMIN_ROLE_PRIVILEGE, $roleId, $privilege);
        }
        return json_decode($privilege, true);
    }
}
