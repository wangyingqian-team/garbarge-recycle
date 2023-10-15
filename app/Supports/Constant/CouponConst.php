<?php

namespace App\Supports\Constant;

class CouponConst
{

    /**========================
     * 优惠券公共常量、
     * ========================
     */


    const THROW_WAIT_STATUS = 1; //待使用

    const THROW_USED_STATUS = 2; //已使用

    const THROW_EXPIRE_STATUS = 3; //已过期

    const THROW_FREEZE_STATUS = 4; //已作废

    const THROW_ORIGIN_ONE = 1; //获取途径：签到

    const THROW_ORIGIN_MAPS = [
        self::THROW_ORIGIN_ONE => '签到'
    ];

}
