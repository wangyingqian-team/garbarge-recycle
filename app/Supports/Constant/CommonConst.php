<?php
namespace App\Supports\Constant;

/**
 * Oss bucket;
 *
 * Class OssBucketConst
 *
 * @package App\Supports\Constant
 */
class CommonConst
{
    /*
    |----------------------------------------
    | 短信相关
    |----------------------------------------
    */
    const SMS_API = 'http://www.smsbao.com/';

    const SMS_USER = 'smsbao_guest';

    const SMS_API_KEY = '97c20ea142f6428fbb3873958370f6d4';

    const SMS_SEND_STATUS = [
        "0" => "短信发送成功",
        "-1" => "参数不全",
        "-2" => "服务器空间不支持,请确认支持curl或者fsocket，联系您的空间商解决或者更换空间！",
        "30" => "密码错误",
        "40" => "账号不存在",
        "41" => "余额不足",
        "42" => "帐户已过期",
        "43" => "IP地址限制",
        "50" => "内容含有敏感词"
    ];
}
