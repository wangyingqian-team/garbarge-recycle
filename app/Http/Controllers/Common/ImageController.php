<?php

namespace App\Http\Controllers\Common;

use App\Exceptions\RestfulException;
use App\Http\Controllers\Controller;
use App\Services\Common\AliOssService;
use App\Supports\Constant\ImageTypeConst;
use Gregwar\Captcha\PhraseBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redis;
use Gregwar\Captcha\CaptchaBuilder;
use Illuminate\Support\Facades\Session;

class ImageController extends Controller
{
    /** @var  AliOssService */
    protected $service;

    public function init()
    {
        $this->service = app(AliOssService::class);
    }

    /**
     * 上传图片
     *
     * @return mixed
     */
    public function upload()
    {
        $paths = [];
        $images = Arr::wrap($this->request->file('images'));
        $type = $this->request->get('type');
        $types = ImageTypeConst::IMAGE_TYPE_PATH_MAP;
        if (!isset($types[$type])) {
            throw new RestfulException('图片类型错误');
        }

        foreach ($images as $image) {
            $paths[] = $this->service->setPrefix($types[$type])->upload($image);
        }

        return $this->success($paths);
    }


    /**
     * 图形验证码
     *
    */
    public function captcha(){
        $mobile = $this->request->post('mobile');
        if (!preg_match("/^1\d{10}$/", $mobile)) {
            throw new RestfulException('手机号格式不对!,请输入正确手机号。');
        }
        $redis = Redis::connection('common');
        $chars = '123456789abcefghijklmnpqrstuvwxyz';
        $builder = new PhraseBuilder(4, $chars);
        $captcha = new CaptchaBuilder(null, $builder);

        // 生成验证码
        $captcha->build(100, 40, $font = null);
        // base64 image
        $image = $captcha->inline();
        //value
        $value = $captcha->getPhrase();

        $redis->setex($mobile, 300, $value);


        return $this->success(['code' => 0, 'image'=>$image,'mobile'=>$mobile,'value'=>$value, 'expire' => 300]);


    }

    /**
     * 短信
     */
    public function sms()
    {
        $redis = Redis::connection('common');
        $captcha = $this->request->post('captcha');
        $mobile = $this->request->get('mobile');
        $value = $redis->get($mobile);
        if ($value != $captcha) {
            throw new RestfulException('图形验证码不对！请重新输入。');
        }
        //销毁图形验证码
        $redis->del($mobile);

        //发送短信 todo


        $code = '56809';
        //end


        //短信验证码存入redis
        $redis->hset('sms_code',$mobile,$code);

        return $this->success(['code' => $code, 'mobile'=>$mobile]);
    }
}
