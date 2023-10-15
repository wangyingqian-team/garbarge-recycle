<?php

namespace App\Services\Common;

use App\Exceptions\RestfulException;
use App\Supports\Constant\RedisKeyConst;
use App\Supports\Traits\HttpClientTrait;
use Illuminate\Support\Facades\Redis;
use Throwable;

class WechatService
{
    use HttpClientTrait;

    private $appId;

    private $appSecret;

    public function __construct()
    {
        $this->appId = config('wechat.app_id');
        $this->appSecret = config('wechat.app_secret');

        $this->setLogChannel('wechat');
        $this->closeBeforeRequestLog();
        $this->closeAfterRespondLog();
        $this->setOptions(['verify' => false]);
        $this->setBaseUri(config('wechat.base_url'));
    }

    public function getToken()
    {
        $redis = Redis::connection();
        $token = $redis->get(RedisKeyConst::ACCESS_TOKEN);
        if (empty($token)) {
            $url = 'cgi-bin/token';

            try {
                $result = $this->get(
                    $url,
                    [
                        'grant_type' => 'client_credential',
                        'appid' => $this->appId,
                        'secret' => $this->appSecret
                    ]
                );
            } catch (Throwable $ex) {
                throw new RestfulException('获取token失败');
            }

            $token = $result['access_token'];
            $redis->setex(RedisKeyConst::ACCESS_TOKEN, $result['expires_in'] - 100, $token);
        }

        return $token;
    }

    /**
     * 获取openid
     *
     * @param $code
     * @return mixed
     */
    public function getOpenid($code) {
        $url = 'sns/oauth2/access_token';
        $params = [
            'code' =>$code,
            'appid' => $this->appId,
            'secret' => $this->appSecret,
            'grant_type' =>'authorization_code'
        ];
        $result = $this->get($url, $params);

        if (!isset($result['openid'])) {
            throw new RestfulException($result['errmsg'] ?? '获取openid失败');
        }

        return $result['openid'];
    }

    /**
     * 获取个人信息
     *
     * @param $openid
     * @return bool|mixed
     */
    public function getUserInfo($openid) {
        $url = 'sns/userinfo';
        $params = [
            'access_token' => $this->getToken(),
            'openid' => $openid,
            'lang' => 'zh_CN',
        ];

        $result = $this->get($url, $params);

        if (!isset($result['openid'])) {
            throw new RestfulException('获取个人信息失败');
        }

        return $result;
    }

    /**
     * 获取小程序二维码
     * @param $scene
     * @param $width
     * @param $lineColor
     * @param $page
     * @param $isOpacity
     * @return mixed
     */
    public function getQRCode($scene, $width, $isOpacity, $page)
    {
        $url = 'wxa/getwxacodeunlimit?access_token=' . $this->getToken();;

        !empty($page) && $param['page'] = $page;
        !empty($isOpacity) && $param['is_hyaline'] = $isOpacity;
        $param = [
            'scene' => $scene,
            'width' => $width,
        ];

        return $this->post($url, $param);
    }

    /**
     * 发送消息模板
     *
     * @param $template
     * @param $openId
     * @param $data
     * @param $page
     * @param $state
     * @return bool|mixed
     */
    public function sendTemplateMsg($template, $openId, $data, $page, $state)
    {
        $url = 'cgi-bin/message/subscribe/send?access_token=' . $this->getToken();
        $params = [
            'touser' => $openId,
            'template_id' => $template['id'],
            'data' => $data,
            'miniprogram_state' => $state
        ];
        $page && $params['page'] = $page;

        return $this->post($url, $params);
    }

    /**
     * 微信小程序关键数据解密
     * @param $encryptedData
     * @param $iv
     * @param $appId
     * @param $sessionKey
     * @return int|string
     */
    public function decryptData($encryptedData, $iv, $appId, $sessionKey)
    {
        if (strlen($sessionKey) != 24) {
            throw new RestfulException('aes key 错误');
        }

        if (strlen($iv) != 24) {
            throw new RestfulException('iv 错误');
        }

        $aesKey = base64_decode($sessionKey);
        $aesIV = base64_decode($iv);

        $result = aes_decrypt($encryptedData, $aesKey, $aesIV);
        $dataObj = json_decode($result);

        if ($dataObj == null || $dataObj->watermark->appid != $appId) {
            throw new RestfulException('获取结果失败');
        }

        return $result;
    }
}
