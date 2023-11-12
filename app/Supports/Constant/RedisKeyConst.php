<?php

namespace App\Supports\Constant;

class RedisKeyConst
{

    /**========================
     * 微信相关key
     * ========================
     */

    const ACCESS_TOKEN = 'wx_access_token';


    /**========================
     * admin 管理后台相关key
     * ========================
     */
    const ADMIN_TOKEN = 'admin_token';

    const ADMIN_ROLE_PRIVILEGE = 'admin_role_privilege';

    const ADMIN_SITE = 'admin_site';

    /**========================
     * 小程序端 user 相关key
     * ========================
     */

    const TODAY_NEWER = 'today_newer'; //今日新增用户

    const USER_SIGN = 'user_sign'; //用户签到

    const TODAY_USER_INCOME = 'today_user_income'; //用户今日收益


    /*
    |----------------------------------------
    | 小程序端 代仍 相关key
    |----------------------------------------
    */
    /**
     * 今日预约代仍总订单数统计Key.
     */
    const THROW_RESERVED_ORDER_COUNT_TODAY = 'throw:reservedOrderCount:today';

    /**
     * 回收员指定时间段的代仍单数Key前缀.
     */
    const THROW_RECYCLER_ORDER_COUNT_PREFIX = 'throw:recyclerOrderCount:';


    /*
    |----------------------------------------
    | 小程序端 回收 相关key
    |----------------------------------------
    */
    /**
     * 今日预约回收总订单数统计Key.
     */
    const RECYCLE_RESERVED_ORDER_COUNT_TODAY = 'recycle:reservedOrderCount:today';

    /**
     * 回收员指定时间段的回收单数Key前缀.
     */
    const RECYCLE_RECYCLER_ORDER_COUNT_PREFIX = 'recycle:recyclerOrderCount:';

    /**
     * 用户回收通知信息Key.
     */
    const RECYCLE_NOTICE_USER = 'recycle:notice:user';

    /**
     * 回收员回收通知信息Key.
     */
    const RECYCLE_NOTICE_RECYCLER = 'recycle:notice:recycler';

    /**
     * 用户收益key.
     */
    const USER_INCOME = 'user_income';


}
