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

    /**
     * 获取用户详情
     *
     * @return mixed
     */
    public function getUserDetail()
    {
        /** @var UserService $userService */
        $userService = app(UserService::class);

        return $this->success($userService->getUserDetail($this->userId));
    }

    public function updateInfo()
    {
        /** @var UserService $userService */
        $userService = app(UserService::class);
        $val = [
            'nickname' => $this->request->get('nickname'),
            'avatar' => $this->request->get('headimgurl'),
            'mobile' => $this->request->get('mobile')
        ];
        return $this->success($userService->update($val));
    }

    /**
     * 签到
     *
     * @return mixed
     */
    public function sign()
    {
        app(UserService::class)->sign($this->userId);
        return $this->success();
    }

    /**
     * 创建地址
     *
     * @return mixed
     */
    public function createAddress()
    {
        $data = [
            'user_id' => $this->userId,
            'village_id' => $this->request->get('village_id'),
            'mobile' => $this->request->get('mobile'),
            'address' => $this->request->get('address'),
            'is_default' => $this->request->get('is_default', false)
        ];
        /** @var UserService $userService */
        $userService = app(UserService::class);
        $userService->createAddress($this->userId, $data);

        return $this->success();
    }

    /**
     * 更新地址
     *
     * @return mixed
     */
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

        /** @var UserService $userService */
        $userService = app(UserService::class);
        $userService->updateAddress($this->userId, $data);

        return $this->success();
    }

    /**
     * 删除地址
     *
     * @return mixed
     */
    public function deleteAddress()
    {
        $id = $this->request->get('id');
       app(UserService::class)->deleteAddress($id);
        return $this->success();
    }

    //获取地址列表
    public function getAddressList()
    {
        $data = app(UserService::class)->getAddressList($this->userId);
        return $this->success($data);
    }

    //获取地址详情
    public function getAddressDetail()
    {
        $data = app(UserService::class)->getAddressDetail($this->request->get('id'));

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
