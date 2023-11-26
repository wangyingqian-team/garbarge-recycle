<?php

namespace App\Http\Controllers\Official;


use App\Services\Activity\ChouService;
use App\Services\Activity\CouponService;
use App\Services\Activity\InvitationService;
use App\Services\Common\WechatService;
use App\Services\User\AddressService;
use App\Services\User\UserService;
use App\Services\User\VillageService;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;


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
        $openid = $this->request->get('openid');

        return $this->success($userService->getUserDetail($openid));
    }

    public function updateInfo()
    {
        /** @var UserService $userService */
        $userService = app(UserService::class);
        $val = [
            'nickname' => $this->request->get('nickname'),
            'avatar' => $this->request->get('headimgurl'),
            'mobile' => $this->request->get('mobile'),
            'sex' => $this->request->get('sex'),
            'user_id' => $this->userId
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

    public function getVillageList()
    {
        /** @var VillageService $villageService */
        $villageService = app(VillageService::class);
        $data = $villageService->getVillageList();
        return $this->success($data);
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
        /** @var AddressService $addressService */
        $addressService = app(AddressService::class);
        $addressService->createAddress($this->userId, $data);

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
            'village_id' => $this->request->get('village_id'),
            'mobile' => $this->request->get('mobile'),
            'address' => $this->request->get('address'),
            'is_default' => $this->request->get('is_default', false)
        ];

        /** @var AddressService $addressService */
        $addressService = app(AddressService::class);
        $addressService->updateAddress($id, $data);

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
       app(AddressService::class)->deleteAddress($id);
        return $this->success();
    }

    //获取地址列表
    public function getAddressList()
    {
        $data = app(AddressService::class)->getAddressList($this->userId);
        return $this->success($data);
    }

    //获取地址详情
    public function getAddressDetail()
    {
        $data = app(AddressService::class)->getAddressDetail($this->request->get('id'));

        return $this->success($data);
    }


    //领取优惠券
    public function getCoupon()
    {
        $couponId = $this->request->get('coupon_id');
        $expire = Carbon::now()->addDays(90);
        /** @var CouponService $couponService */
        $couponService = app(CouponService::class);
        $couponService->obtainCoupon($this->userId, $couponId, $expire);

        return $this->success(true);
    }

    //优惠券详情
    public function getCouponDetail()
    {
        $couponId = $this->request->get('coupon_id');
        /** @var CouponService $couponService */
        $couponService = app(CouponService::class);
        $detail = $couponService->getCouponDetail( $couponId);

        return $this->success($detail);
    }

    //优惠券列表
    public function getCouponList()
    {
        $status = $this->request->get('status');
        /** @var CouponService $couponService */
        $couponService = app(CouponService::class);
        $list = $couponService->getCouponList($this->userId, $status);
        $data = [
            'pz' => [
                'name' => '膨胀券',
                'items' => []
            ],
            'dj' => [
                'name' => '代金券',
                'items' => []
            ],
            'dh' => [
                'name' => '兑换券',
                'items' => []
            ],
            'hf' => [
                'name' => '话费券',
                'items' => []
            ],
        ];
        foreach ($list as $item){
            switch ($item['coupon']['type']) {
                case 1 :
                    $data['hf']['items'][] = $item;
                    break;
                case 2:
                    $data['pz']['items'][] = $item;
                    break;
                case 3:
                    $data['dj']['items'][] = $item;
                    break;
                case 4:
                    $data['dh']['items'][] = $item;
                    break;
            }
        }

        return $this->success($data);
    }

    //绿豆列表
    public function getBeanList()
    {
        /** @var InvitationService $service */
        $service = app(InvitationService::class);
        $page = $this->request->get('page');
        $pageSize = $this->request->get('page_size', 10);
        $data = $service->getBeanRecord($this->userId,$page, $pageSize);

        return $this->success($data);
    }

    //推广列表(下级列表)
    public function getSubInvitationList()
    {
        /** @var InvitationService $service */
        $service = app(InvitationService::class);
        //下级列表
        $data = $service->getUserSubInvitation($this->userId);

        return $this->success($data);
    }

    //抽奖
    public function chou()
    {
        /** @var ChouService $service */
        $service = app(ChouService::class);
        $data = $service->chou($this->userId);

        return $this->success($data);
    }
}
