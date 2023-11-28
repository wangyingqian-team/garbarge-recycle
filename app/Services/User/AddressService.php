<?php

namespace App\Services\User;

use App\Models\UserAddressModel;
use App\Supports\Constant\UserConst;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * xi
 *
 * Class AssertService
 * @package App\Services\User
 */
class AddressService
{
    /**
     * @param $userId
     * @param $data
     * @return bool
     * @throws \Throwable
     */
    public function createAddress($userId, $data)
    {

        DB::transaction(
            function () use ($userId, $data) {
                if ($data['is_default'] ?? false) {
                    $oldDefault = UserAddressModel::query()->where(['user_id' => $userId, 'is_default' => UserConst::IS_DEFAULT_ADDRESS])->macroFirst();
                    if (!empty($oldDefault)) {
                        UserAddressModel::query()->whereKey($oldDefault['id'])->update(['is_default' => UserConst::IS_NOT_DEFAULT_ADDRESS]);
                    }
                }

                $iData = [
                    'user_id' => $userId,
                    'village_id' => $data['village_id'],
                    'contacts' => $data['contacts'],
                    'address' => $data['address'],
                    'mobile' => $data['mobile'],
                    'is_default' => $data['is_default'],
                ];
                UserAddressModel::query()->insert($iData);
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
        DB::transaction(
            function () use ($id, $data) {

                if ($data['is_default'] ?? false) {
                    $oldDefault = UserAddressModel::query()->where(['user_id' => $id, 'is_default' => UserConst::IS_DEFAULT_ADDRESS])->macroFirst();
                    if (!empty($oldDefault)) {
                        UserAddressModel::query()->whereKey($oldDefault['id'])->update(['is_default' => UserConst::IS_NOT_DEFAULT_ADDRESS]);
                    }
                }

                $iData = [
                    'village_id' => $data['village_id'] ?? null,
                    'address' => $data['address'] ?? null,
                    'mobile' => $data['mobile'] ?? null,
                    'is_default' => $data['is_default'] ?? null,
                    'contacts' => $data['contacts'] ??null,
                ];

                $iData = array_null($iData);
                if (!empty($iData)) {
                    UserAddressModel::query()->whereKey($id)->update($iData);
                }
            });

        return $id;
    }

    //删除地址
    public function delAddress($id) {
        UserAddressModel::query()->whereKey($id)->delete();
    }

    //地址列表
    public function getAddressList($userId) {
        $data = UserAddressModel::query()->where('user_id', $userId)->get()->toArray();
        foreach ($data as $k => $datum) {
            if ($datum['is_default'] == 1) {
                $default =$datum;
                unset($data[$k]);
                $data = array_merge([$default], $data);
                break;
            }
        }

        return $data;

    }

    //地址列表
    public function getAddressDetail($id) {
        return UserAddressModel::query()->whereKey($id)->macroFirst();
    }
}
