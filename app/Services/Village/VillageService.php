<?php

namespace App\Services\Village;

use App\Dto\VillageDto;
use App\Dto\VillageFloorDto;
use App\Exceptions\RestfulException;
use App\Services\GarbageRecycle\GarbageSiteService;
use App\Services\User\UserAddressService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class VillageService
{
    /**
     * @param $data
     * @return bool
     * @throws \Throwable
     */
    public function createVillage($data)
    {
        DB::transaction(
            function () use ($data) {
                $floors = Arr::pull($data, 'floors');
                $id = app(VillageDto::class)->create($data);
                app(VillageFloorDto::class)->create($id, $floors);
            }
        );

        return true;
    }

    /**
     * @param $id
     * @param $data
     * @throws \Throwable
     */
    public function updateVillage($id, $data)
    {
        DB::transaction(
            function () use ($id, $data) {
                $floors = Arr::pull($data, 'floors');
                app(VillageDto::class)->update($id, $data);

                if (isset($floors['add']) && !empty($floors['add'])) {
                    app(VillageFloorDto::class)->create($id, $floors['add']);
                }

                if (isset($floors['delete']) && !empty($floors['delete'])) {
                    app(VillageFloorDto::class)->delete($floors['delete']);
                }
            }
        );

        return true;
    }

    /**
     * 删除
     *
     * @param $villageId
     * @return bool
     * @throws \Throwable
     */
    public function deleteVillage($villageId) {
        //小区没有被用户使用
        $exists = app(UserAddressService::class)->checkVillageUsedBeAddress($villageId);
        if ($exists) {
            throw new RestfulException('该小区已被使用，无法删除');
        }

        DB::transaction(function () use($villageId) {
            app(VillageDto::class)->delete($villageId);
            app(VillageFloorDto::class)->deleteByVillageId($villageId);
        });

        return true;

    }

    public function getVillageList(
        $where = [],
        $select = ['*'],
        $orderBy = ['create_time' => 'desc'],
        $page = 0,
        $pageSize = 0,
        $withPage = false
    ) {
        return app(VillageDto::class)->getList($where, $select, $orderBy, $page, $pageSize, $withPage);
    }


    public function getVillageDetail($id, $select = ['*'])
    {
        return app(VillageDto::class)->getDetail($id, $select);
    }

    public function getVillageFloorDetail($id)
    {
        return app(VillageFloorDto::class)->getFloorDetail($id);
    }


    public function getNearVillageList($province, $city, $area)
    {
        $where = [
            'province' => $province,
            'city' => $city,
            'area' => $area
        ];
        $siteIds = app(GarbageSiteService::class)->getGarbageSiteList($where, ['id']);

        $data = app(VillageDto::class)->getList(
            ['site_id|in' => Arr::flatten($siteIds)],
            ['id', 'name', 'floor.id', 'floor.village_id', 'floor.floor']
        );

        return $data;
    }
}
