<?php

namespace App\Services\GarbageRecycle;

use App\Dto\GarbageSellStationDto;
use App\Dto\GarbageSiteDto;
use App\Supports\Constant\RedisKeyConst;
use Illuminate\Support\Facades\Redis;

class GarbageSiteService
{

    public function createGarbageSite($data)
    {
        return app(GarbageSiteDto::class)->create($data);
    }


    public function updateGarbageSite($id, $data)
    {
        app(GarbageSiteDto::class)->update($id, $data);

        if (isset($data['admin_id'])) {
            $redis = Redis::connection('admin');
            $redis->hdel(RedisKeyConst::ADMIN_SITE, $data['admin_id']);
        }

        return true;
    }

    public function deleteGarbageSite($id)
    {
        app(GarbageSiteDto::class)->delete($id);
        $redis = Redis::connection('admin');
        $redis->del(RedisKeyConst::ADMIN_SITE);

        return true;
    }

    public function getGarbageSiteDetail($id, $select = ['*'])
    {
        return app(GarbageSiteDto::class)->getDetailById($id, $select);
    }

    /**
     * 查询垃圾回收站列表
     *
     * @param array $where
     * @param array $select
     * @param array $orderBy
     * @param int $page
     * @param int $limit
     * @param boolean $withPage
     *
     * @return mixed
     *
     */
    public function getGarbageSiteList($where, $select, $orderBy = [], $page = 0, $limit = 0, $withPage = true)
    {
        return app(GarbageSiteDto::class)->getList($where, $select, $orderBy, $page, $limit, $withPage);
    }

    public function getGarbageIdByAdminId($adminId)
    {
        $redis = Redis::connection('admin');
        $id = $redis->hget(RedisKeyConst::ADMIN_SITE, $adminId);
        if (empty($id)) {
            $id = app(GarbageSiteDto::class)->getIdByAdminId($adminId);
            $redis->hset(RedisKeyConst::ADMIN_SITE, $adminId, $id);
        }
        return $id;
    }

}
