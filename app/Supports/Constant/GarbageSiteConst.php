<?php

namespace App\Supports\Constant;

class GarbageSiteConst
{
    /*
    |----------------------------------------
    | 站点营业状态
    |----------------------------------------
    */
    /**
     * 站点营业状态：营业
     */
    const GARBAGE_SITE_STATUS_WORKING = 1;

    /**
     * 站点营业状态：非营业
     */
    const GARBAGE_SITE_STATUS_RESTING = 2;

    /*
    |----------------------------------------
    | 站点是否支持代仍
    |----------------------------------------
    */
    /**
     * 站点是否支持代仍：支持
     */
    const GARBAGE_SITE_THROW_IS_SUPPORT = 1;

    /**
     * 站点是否支持代仍：不支持
     */
    const GARBAGE_SITE_THROW_IS_NOT_SUPPORT = 2;

    /*
    |----------------------------------------
    | 站点是否支持回收
    |----------------------------------------
    */
    /**
     * 站点是否支持回收：支持
     */
    const GARBAGE_SITE_RECYCLE_IS_SUPPORT = 1;

    /**
     * 站点是否支持回收：不支持
     */
    const GARBAGE_SITE_RECYCLE_IS_NOT_SUPPORT = 2;
}