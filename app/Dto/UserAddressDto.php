<?php

namespace App\Dto;

use App\Supports\Constant\UserConst;

class UserAddressDto extends Dto
{

    public function createAddress($userId, $data)
    {
        $iData = [
            'user_id' => $userId,
            'village_id' => $data['village_id'],
            'village_floor_id' => $data['village_floor_id'],
            'address' => $data['address'],
            'mobile' => $data['mobile'],
            'is_default' => $data['is_default'],
        ];

        return $this->query->insertGetId($iData);
    }

    public function updateAddress($id, $data)
    {
        $iData = [
            'village_id' => $data['village_id'] ?? null,
            'village_floor_id' => $data['village_floor_id'] ?? null,
            'address' => $data['address'] ??null,
            'mobile' => $data['mobile'] ?? null,
            'is_default' => $data['is_default'] ?: UserConst::IS_NOT_DEFAULT_ADDRESS,
        ];

        $iData = array_null($iData);
        if (!empty($iData)) {
            $this->query->whereKey($id)->update($iData);
        }
        return true;
    }

    public function getDefaultAddressByUserId($userId)
    {
        return $this->query->where(['user_id'=> $userId, 'is_default'=>UserConst::IS_DEFAULT_ADDRESS])->macroFirst();
    }

    public function changeDefaultAddress($id, $isDefault)
    {
        return $this->query->whereKey($id)->update(['is_default' => $isDefault]);
    }

    public function getAddressListByUserId($userId)
    {
        return $this->query->where('user_id', $userId)->get()->toArray();
    }

    public function getAddressById($id)
    {
        return $this->query->whereKey($id)->macroFirst();
    }

    public function deleteAddress($id)
    {
        return $this->query->whereKey($id)->delete();
    }

    public function getOneAddressByVillageId($villageId)
    {
        return $this->query->where('village_id', $villageId)->macroFirst();
    }

    public function getAddressByVillageIds($villageIds)
    {
        return $this->query->whereIn('village_id', $villageIds)->get()->toArray();
    }

}
