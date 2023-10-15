<?php

namespace App\Http\Controllers\Official;

use App\Services\Activity\InvitationService;
use App\Services\Activity\NewerService;
use App\Services\Common\WechatService;
use App\Services\User\UserAddressService;
use App\Services\User\UserAssetsService;
use App\Services\User\UserService;
use App\Services\Village\VillageService;
use App\Supports\Constant\ActivityConst;

class UserController extends BaseController
{

    /**
     * 用户注册
     *
     * @return mixed
     */
    public function register()
    {
        $wxService = app(WechatService::class);
        $code = $this->request->get("code");
        $openid = $wxService->getOpenid($code);
        $userInfo = $wxService->getUserInfo($openid);

        $userId = app(UserService::class)->create($userInfo);

        return $this->success($userId);
    }

    public function getUserDetail()
    {
        $userService = app(UserService::class);

        return $this->success($userService->getUserDetail($this->userId));
    }

    public function sign()
    {
        app(UserService::class)->sign($this->userId);
        return $this->success();
    }

    public function getNearVillageList()
    {
        $province = $this->request->get('province');
        $city = $this->request->get('city');
        $area = $this->request->get('area');

        $data = app(VillageService::class)->getNearVillageList($province, $city, $area);

        return $this->success($data);
    }

    public function createAddress()
    {
        $data = [
            'village_id' => $this->request->get('village_id'),
            'village_floor_id' => $this->request->get('village_floor_id'),
            'mobile' => $this->request->get('mobile'),
            'address' => $this->request->get('address'),
            'is_default' => $this->request->get('is_default', false)
        ];
        app(UserAddressService::class)->createAddress($this->userId, $data);

        return $this->success();
    }

    public function updateAddress()
    {
        $id = $this->request->get('id');
        $data = [
            'user_id' => $this->userId,
            'village_id' => $this->request->get('village_id'),
            'village_floor_id' => $this->request->get('village_floor_id'),
            'mobile' => $this->request->get('mobile'),
            'address' => $this->request->get('address'),
            'is_default' => $this->request->get('is_default', false)
        ];

        app(UserAddressService::class)->updateAddress($id, $data);

        return $this->success();
    }

    public function deleteAddress()
    {
        $id = $this->request->get('id');
        app(UserAddressService::class)->deleteAddress($id);
        return $this->success();
    }

    public function getAddressList()
    {
        $data = app(UserAddressService::class)->getAddressListByUserId($this->userId);
        return $this->success($data);
    }

    public function getAddressDetail()
    {
        $data = app(UserAddressService::class)->getAddressById($this->request->get('id'));

        return $this->success($data);
    }

    /**
     * 领取奖励
     *
     * @return mixed
     */
    public function receiveActivityReward() {
        $activityId = $this->request->get('activity_id');
        $type = $this->request->get('type');
        $reward = $this->request->get('reward');
        app(UserService::class)->completeActivity($this->userId,$activityId, $type,$reward);

        return $this->success();
    }

    public function getInviteActivityList() {
        $data['count'] = app(UserService::class)->getUserInvitationCount($this->userId);
        $data['list'] = app(InvitationService::class)->getInvitationList();
        $receivedLog = app(UserService::class)->getActivityLog($this->userId,ActivityConst::ACTIVITY_INVITE);
        $receivedLog = collect($receivedLog)->keyBy('activity_id');
        foreach ($data['list'] as &$value){
            $value['can_receive'] = ($data['count'] >= $value['invite_user_count']);
            $value['is_received'] = isset($receivedLog[$value['id']]);
        }

        return $this->success($data);
    }

    public function getNewerActivityList() {
        $type = $this->request->get('type');
        $assets = app(UserAssetsService::class)->getUserAssets($this->userId);
        if ($type == ActivityConst::ACTIVITY_NEWER_THROW) {
            $data['count'] = $assets['throw_total'];
        }else{
            $data['count'] = $assets['recycle_total'];
        }
        $data['list'] = app(NewerService::class)->getActivityList();
        $receivedLog = app(UserService::class)->getActivityLog($this->userId,ActivityConst::ACTIVITY_NEWER);
        $receivedLog = collect($receivedLog)->keyBy('activity_id');
        foreach ($data['list'] as &$value){
            $value['can_receive'] = ($data['count'] >= $value['total_count']);
            $value['is_received'] = isset($receivedLog[$value['id']]);
        }

        return $this->success($data);
    }
}
