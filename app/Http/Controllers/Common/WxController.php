<?php
/**
 * Created by PhpStorm.
 * User: wumx2
 * Date: 2020-3-30
 * Time: 13:56
 */

namespace App\Http\Controllers\Official;

use App\Exceptions\RestfulException;
use App\Http\Controllers\Controller;
use App\Services\Common\WechatService;
use App\Services\Mass\Account;
use App\Supports\Constant\ConfigConst;
use App\Supports\Constant\ServiceConst;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Storage;

/**
 * 微信接口相关
 * Class WxController
 * @package App\Http\Controllers\User
 */
class WxController extends Controller
{
    /** @var WechatService */
    protected $service;

    public function init()
    {
        $this->service = get_driver(ServiceConst::COMMON_MANAGER, ServiceConst::COMMON_WECHAT);
    }

    /**
     * 获取公众号openid
     * @return string
     * @throws \GuzzleHttp\GuzzleException
     */
    public function getOpenid()
    {
        $code = $this->request->get("code");
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . config('wechat.app_id') . '&secret=' . config('wechat.app_secret') . '&code=' . $code . '&grant_type=authorization_code';
        $client = new Client();

        $res = $client->request('GET', $url, ['verify' => false]);

        $data = $res->getBody()->getContents();

        $data = json_decode($data, true);

        if (!isset($data['openid'])) {
            throw new RestfulException($data['errmsg'] ?? '获取openid失败');
        }

        $url = "https://api.weixin.qq.com/sns/userinfo?access_token=" . $data['access_token'] . "&openid=" . $data['openid'] . "&lang=zh_CN";
        $res = $client->request('GET', $url, ['verify' => false]);
        $data = $res->getBody()->getContents();
        $data = json_decode($data, true);
        if (!isset($data['openid'])) {
            throw new RestfulException('获取个人信息失败');
        }

        /** @var Account $userService */
        $userService = get_driver(ServiceConst::MASS_MANAGER, ServiceConst::MASS_ACCOUNT);
        $row = $userService->userExist($data['openid']);
        if (empty($row['id'])) {
            $row = [
                'nickname' => $data['nickname'],
                'sex' => $data['sex'],
                'avatar' => $data['headimgurl'],
                'openid' => $data['openid']
            ];
            $row['id'] = $userService->addUser($row);
        }

        $row['user_token'] = md5('user_token');

        return $this->success($row);
    }

    /**
     * 获取个人码
     *
     * @return mixed
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getPersonalQRCode()
    {
        $userId = $this->request->get('user_id', 99999);

        $token = $this->service->getAccessToken();

        $params = [
            'verify' => false,
            'json' => [
                'action_name' => 'QR_LIMIT_SCENE',
                'action_info' => [
                    'scene' => [
                        'user_id' => $userId
                    ]
                ],
            ]
        ];
        $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=' . $token;

        $client = new Client();
        $res = $client->request('POST', $url, $params);
        $data = json_decode($res->getBody()->getContents(), true);

        if (isset($data['ticket'])) {
            $url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . urlencode($data['ticket']);
            $res = $client->request('GET', $url, ['verify' => false]);
            $img = $res->getBody()->getContents();

        } else {
            throw new RestfulException('获取个人码失败');
        }

        $path = 'personal_code_' . $userId;
        Storage::disk('local')->put($path, $img);

        return $path;

//        header("Content-type: image/jpeg");
//
//        echo $img;
//        exit;
    }

    /**
     * 微信手机号码解析
     * @return mixed
     */
    public function bizData()
    {
        $sessionKey = $this->request->post("sessionKey");
        $openid = $this->request->post("openid", "");
        $encryptedData = $this->request->post("encryptedData");
        $iv = $this->request->post("iv");
        /** @var Config $config */
        $config = get_driver(ServiceConst::CONFIG_MANAGER, ServiceConst::CONFIG_CONFIG);
        $appId = $config->getConfig(ConfigConst::MINI_APP_ID);
        $deviceInfo = $this->request->post("info");

        //微信小程序信息解密
        $data = WxUtil::decryptData($encryptedData, $iv, $appId, $sessionKey);

        $result = json_decode($data, true);

        //$phone = $result['phoneNumber'];

        return $this->success($result);
    }

    /**
     * 获取小程序二维码
     * @return mixed
     */
    public function getQRCode()
    {
        $scene = $this->request->get("scene", 1);
        $width = $this->request->get("width", 430); //二维码的宽度，单位 px，最小 280px，最大 1280px
        //$autoColor = $this->request->post("autoColor",""); //自动配置线条颜色，如果颜色依然是黑色，则说明不建议配置主色调，默认 false
        //$lineColor = $this->request->post("lineColor",'{"r":0,"g":0,"b":0}'); //auto_color 为 false 时生效，使用 rgb 设置颜色 例如 {"r":"xxx","g":"xxx","b":"xxx"} 十进制表示
        $isOpacity = $this->request->get("opacity", ""); //是否需要透明底色，为 true 时，生成透明底色的小程序
        $page = $this->request->get("page", "");


        $res = $this->service->getQRCode($scene, $width, $isOpacity, $page);

        header("Content-type: image/jpeg");

        echo $res;
        exit;
    }

    public function getJSSDKConfig()
    {
        $url = $this->request->post('url');

        $result = $this->service->getJSSDKConfig($url);

        return $this->success($result);
    }

    /**
     * 测试发送模板消息
     *
     * @return mixed
     *
     * @throws \Throwable
     */
    public function sendTemplateMessage()
    {
        $openId = $this->request->post('openid');
        $templateId = $this->request->post('template_id');

        $sendData = [
            'first' => [
                "value" => "供奉佛像成功通知",
                "color" => "#173177"
            ],
            'keyword1' => [
                "value" => 'C20210505110928',
                "color" => "#173177"
            ],
            'keyword2' => [
                "value" => '观世音菩萨',
                "color" => "#173177"
            ],
            'keyword3' => [
                "value" => '2021-05-05 11:09:25',
                "color" => "#173177"
            ],
            'keyword4' => [
                "value" => '2021-05-06 11:09:25',
                "color" => "#173177"
            ],
            'remark' => [
                "value" => '请知悉',
                "color" => "#173177"
            ]
        ];

        $result = $this->service->sendTemplateMessage($openId, $templateId, $sendData, "https://www.qq.com");

        return $this->success($result);
    }

    public function sendTemplateMsg()
    {
        $openid = $this->request->get('open_id');
        $type = $this->request->get('type');
        $data = $this->request->get('data');
        $page = $this->request->get('page');
        $state = $this->request->get('state', 'formal');

        /** @var WechatService $wechat */
        $wechat = get_driver(ServiceConst::COMMON_MANAGER, ServiceConst::WECHAT);
        $result = $wechat->sendTemplateMsg($type, $openid, $data, $page, $state);

        return $this->success($result);
    }

}
