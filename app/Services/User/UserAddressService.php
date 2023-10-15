<?php

namespace App\Services\User;

use App\Dto\UserAddressDto;
use App\Supports\Constant\UserConst;
use DB;

class UserAddressService
{

    /**
     * @param $userId
     * @param $data
     * @return bool
     * @throws \Throwable
     */
    public function createAddress($userId, $data)
    {
        $dto = app(UserAddressDto::class);
        DB::transaction(
            function () use ($userId, $data, $dto) {
                $id = $dto->createAddress($userId, $data);
                if ($data['is_default'] ?? false) {
                    $oldDefault = $dto->getDefaultAddressByUserId($userId);
                    $dto->changeDefaultAddress($oldDefault['id'], UserConst::IS_NOT_DEFAULT_ADDRESS);
                    $dto->changeDefaultAddress($id, UserConst::IS_DEFAULT_ADDRESS);
                }
            }
        );

        return true;
    }


    /**
     * @param $id
     * @param $data
     * @return mixed
     * @throws \Throwable
     */
    public function updateAddress($id, $data)
    {
        $dto = app(UserAddressDto::class);
        DB::transaction(
            function () use ($id, $data, $dto) {
                $dto->updateAddress($id, $data);
                if ($data['is_default'] ?? false) {
                    $oldDefault = $dto->getDefaultAddressByUserId($data['user_id']);
                    $dto->changeDefaultAddress($oldDefault['id'], UserConst::IS_NOT_DEFAULT_ADDRESS);
                    $dto->changeDefaultAddress($id, UserConst::IS_DEFAULT_ADDRESS);
                }
            });

        return $id;
    }

    public function getAddressListByUserId($userId)
    {
        return app(UserAddressDto::class)->getAddressListByUserId($userId);
    }

    public function getAddressById($id)
    {
        return app(UserAddressDto::class)->getAddressById($id);
    }

    public function deleteAddress($id)
    {
        return app(UserAddressDto::class)->deleteAddress($id);
    }

    public function checkVillageUsedBeAddress($villageId) {
        $row =  app(UserAddressDto::class)->getOneAddressByVillageId($villageId);

        return !empty($row);
    }

    public function getAddressByVillageIds($villageIds) {

        return app(UserAddressDto::class)->getAddressByVillageIds($villageIds);
    }

}
