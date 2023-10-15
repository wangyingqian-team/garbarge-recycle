<?php

namespace App\Supports\Constant;

/**
 * Oss bucket;
 *
 * Class OssBucketConst
 *
 * @package App\Supports\Constant
 */
class ImageTypeConst
{
    const DEFAULT_IMAGE_TYPE = 1;  //默认

    const USER_HEAD_IMAGE_TYPE = 2;//用户头像

    const ID_IMAGE_TYPE = 3; //用户身份证

    const GARBAGE_IMAGE_TYPE = 4; //垃圾

    const JIFEN_IMAGE_TYPE = 5;//积分商城

    const ACTIVITY_IMAGE_TYPE = 6;// 活动

    const IMAGE_TYPE_PATH_MAP = [
        self::DEFAULT_IMAGE_TYPE => 'default',
        self::USER_HEAD_IMAGE_TYPE => 'user/head',
        self::ID_IMAGE_TYPE => 'user/id',
        self::GARBAGE_IMAGE_TYPE => 'garbage',
        self::JIFEN_IMAGE_TYPE => 'jifen',
        self::ACTIVITY_IMAGE_TYPE => 'activity'
    ];
}
